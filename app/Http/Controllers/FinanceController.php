<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TopupOrder;
use App\Models\PayoutTransaction;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Services\XenithPayService;

class FinanceController extends Controller
{
    protected XenithPayService $xenithService;

    public function __construct(XenithPayService $xenithService)
    {
        $this->xenithService = $xenithService;
    }

    /**
     * Display Super Admin Finance Dashboard
     */
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403, 'Akses Terbatas: Hanya Pengurus Admin IPPTI yang dapat mengakses Menu Keuangan.');
        }

        PayoutTransaction::ensureTableExists();

        // 1. Live Xenith Balances (safe call to production API)
        $balanceData = [
            'available_balance' => 0,
            'pending_balance' => 0,
            'total_balance' => 0,
            'currency' => 'IDR',
            'success' => false,
        ];
        
        try {
            $balanceData = $this->xenithService->getBalances();
        } catch (\Throwable $e) {
            // fallback gracefully
        }

        // 2. Auto-sync remote payins from Xenith Production API if requested or on load
        try {
            $remotePayins = $this->xenithService->getPayInsList(1, 50);
            if (!empty($remotePayins['data']) && is_array($remotePayins['data'])) {
                foreach ($remotePayins['data'] as $payin) {
                    $ref = $payin['referenceCode'] ?? $payin['reference_id'] ?? $payin['id'] ?? null;
                    if (!$ref) continue;

                    $rawStatus = strtoupper((string)($payin['status'] ?? 'PENDING'));
                    $normStatus = in_array($rawStatus, ['COMPLETED', 'SUCCESS', 'PAID', 'SETTLED']) ? 'success' : (in_array($rawStatus, ['PENDING', 'PROCESSING']) ? 'pending' : 'failed');
                    $amt = (float)($payin['amount'] ?? $payin['initiatedAmount'] ?? $payin['totalAmount'] ?? 0);
                    $channel = $payin['paymentChannel'] ?? $payin['paymentMethod'] ?? 'Xenith Pay';

                    $existing = TopupOrder::where('order_id', $ref)->first();
                    if (!$existing && $amt > 0) {
                        TopupOrder::create([
                            'order_id' => $ref,
                            'user_id' => Auth::id(),
                            'amount_idr' => $amt,
                            'points_issued' => $amt,
                            'conversion_rate' => 1,
                            'status' => $normStatus,
                            'payment_gateway' => 'xenith',
                            'payment_channel' => $channel,
                            'payment_response_text' => json_encode($payin),
                            'created_at' => isset($payin['createdAt']) ? \Carbon\Carbon::parse($payin['createdAt']) : now(),
                        ]);
                    } elseif ($existing && $existing->status !== $normStatus) {
                        $existing->update([
                            'status' => $normStatus,
                            'payment_channel' => $channel,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // safe fallback
        }

        // 3. Inflow (Top-Up Masuk)
        $totalInflow = 0;
        $totalPointsIssued = 0;
        $totalPayinCount = 0;
        $thisMonthInflow = 0;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('topup_orders')) {
                $dbInflow = (float)TopupOrder::where('status', 'success')->sum('amount_idr');
                $totalPayinCount = TopupOrder::where('status', 'success')->count();
                $thisMonthInflow = (float)TopupOrder::where('status', 'success')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount_idr');

                if (\Illuminate\Support\Facades\Schema::hasColumn('topup_orders', 'points_issued')) {
                    $totalPointsIssued = (int)TopupOrder::where('status', 'success')->sum('points_issued');
                }

                // If DB is empty but Xenith API returns live balance, use Xenith live balance as total inflow baseline
                $liveTotal = (float)($balanceData['total_balance'] ?? 0);
                $liveAvail = (float)($balanceData['available_balance'] ?? 0);
                $totalInflow = max($dbInflow, $liveTotal, $liveAvail);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // 4. Outflow (Pencairan / Payout)
        $totalPayoutDisbursed = 0;
        $totalPayoutIppti = 0;
        $totalPayoutBenlaris = 0;
        $pendingPayoutCount = 0;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payout_transactions')) {
                $totalPayoutDisbursed = (float)PayoutTransaction::whereIn('status', ['success', 'simulated'])->sum('amount');
                $totalPayoutIppti = (float)PayoutTransaction::where('recipient_type', 'IPPTI')
                    ->whereIn('status', ['success', 'simulated'])->sum('amount');
                $totalPayoutBenlaris = (float)PayoutTransaction::where('recipient_type', 'BENLARIS')
                    ->whereIn('status', ['success', 'simulated'])->sum('amount');
                $pendingPayoutCount = PayoutTransaction::whereIn('status', ['pending', 'processing'])->count();
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // 5. Available Balances & Bagi Hasil (50% IPPTI & 50% Benlaris)
        $liveAvailable = (float)($balanceData['available_balance'] ?? 0);
        $livePending = (float)($balanceData['pending_balance'] ?? 0);
        $liveTotal = (float)($balanceData['total_balance'] ?? ($liveAvailable + $livePending));

        $readyToDisburse = $liveAvailable > 0 ? $liveAvailable : max(0, $totalInflow - $totalPayoutDisbursed);

        $splitIppti = floor($totalInflow * 0.5);
        $splitBenlaris = floor($totalInflow * 0.5);

        $splitIpptiAvailable = floor($readyToDisburse * 0.5);
        $splitBenlarisAvailable = floor($readyToDisburse * 0.5);

        // 6. Bank Accounts & Settings
        $bankSettings = [
            'bank_name_ippti' => Setting::get('bank_name_ippti', 'Bank BCA'),
            'bank_channel_ippti' => Setting::get('bank_channel_ippti', 'CENAIDJA'),
            'bank_account_ippti' => Setting::get('bank_account_ippti', '5555637653'),
            'bank_holder_ippti' => Setting::get('bank_holder_ippti', 'IKATAN PENERJEMAH INDONESIA'),
            
            'bank_name_benlaris' => Setting::get('bank_name_benlaris', 'Bank Mandiri'),
            'bank_channel_benlaris' => Setting::get('bank_channel_benlaris', 'BMRIIDJA'),
            'bank_account_benlaris' => Setting::get('bank_account_benlaris', '1370019283741'),
            'bank_holder_benlaris' => Setting::get('bank_holder_benlaris', 'PT BENLARIS SUKSES INDONESIA'),

            'auto_payout_monthly' => Setting::get('auto_payout_monthly', '1'),
            'min_payout_threshold' => (float)Setting::get('min_payout_threshold', 100000),
        ];

        // 7. Transactions History
        try {
            $payinOrders = TopupOrder::with('user')
                ->orderBy('id', 'desc')
                ->paginate(15, ['*'], 'payin_page');
        } catch (\Throwable $e) {
            $payinOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        try {
            $payoutTransactions = PayoutTransaction::with('user')
                ->orderBy('id', 'desc')
                ->paginate(15, ['*'], 'payout_page');
        } catch (\Throwable $e) {
            $payoutTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return view('admin.finance', compact(
            'balanceData',
            'totalInflow',
            'totalPointsIssued',
            'totalPayinCount',
            'thisMonthInflow',
            'totalPayoutDisbursed',
            'totalPayoutIppti',
            'totalPayoutBenlaris',
            'pendingPayoutCount',
            'readyToDisburse',
            'splitIppti',
            'splitBenlaris',
            'splitIpptiAvailable',
            'splitBenlarisAvailable',
            'bankSettings',
            'payinOrders',
            'payoutTransactions'
        ));
    }

    /**
     * Manual Trigger to Sync Live Xenith Pay Data
     */
    public function syncLiveXenithData(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        try {
            $balance = $this->xenithService->getBalances();
            $payins = $this->xenithService->getPayInsList(1, 100);

            $syncedCount = 0;
            if (!empty($payins['data']) && is_array($payins['data'])) {
                foreach ($payins['data'] as $payin) {
                    $ref = $payin['referenceCode'] ?? $payin['reference_id'] ?? $payin['id'] ?? null;
                    if (!$ref) continue;

                    $rawStatus = strtoupper((string)($payin['status'] ?? 'PENDING'));
                    $normStatus = in_array($rawStatus, ['COMPLETED', 'SUCCESS', 'PAID', 'SETTLED']) ? 'success' : (in_array($rawStatus, ['PENDING', 'PROCESSING']) ? 'pending' : 'failed');
                    $amt = (float)($payin['amount'] ?? $payin['initiatedAmount'] ?? $payin['totalAmount'] ?? 0);
                    $channel = $payin['paymentChannel'] ?? $payin['paymentMethod'] ?? 'Xenith Pay';

                    $existing = TopupOrder::where('order_id', $ref)->first();
                    if (!$existing && $amt > 0) {
                        TopupOrder::create([
                            'order_id' => $ref,
                            'user_id' => Auth::id(),
                            'amount_idr' => $amt,
                            'points_issued' => $amt,
                            'conversion_rate' => 1,
                            'status' => $normStatus,
                            'payment_gateway' => 'xenith',
                            'payment_channel' => $channel,
                            'payment_response_text' => json_encode($payin),
                            'created_at' => isset($payin['createdAt']) ? \Carbon\Carbon::parse($payin['createdAt']) : now(),
                        ]);
                        $syncedCount++;
                    } elseif ($existing) {
                        $existing->update([
                            'status' => $normStatus,
                            'payment_channel' => $channel,
                        ]);
                    }
                }
            }

            $avail = number_format($balance['available_balance'] ?? 0, 0, ',', '.');
            return back()->with('success', "Sinkronisasi berhasil! Saldo Realtime Xenith: Rp {$avail}. ({$syncedCount} transaksi baru diperbarui).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyinkronkan data Xenith: ' . $e->getMessage());
        }
    }

    /**
     * Update Bank Accounts & Auto-Payout Schedule Settings
     */
    public function updateSettings(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        $request->validate([
            'bank_name_ippti' => 'required|string|max:100',
            'bank_channel_ippti' => 'required|string|max:50',
            'bank_account_ippti' => 'required|string|max:100',
            'bank_holder_ippti' => 'required|string|max:150',

            'bank_name_benlaris' => 'required|string|max:100',
            'bank_channel_benlaris' => 'required|string|max:50',
            'bank_account_benlaris' => 'required|string|max:100',
            'bank_holder_benlaris' => 'required|string|max:150',

            'min_payout_threshold' => 'required|numeric|min:10000',
        ]);

        Setting::set('bank_name_ippti', $request->bank_name_ippti);
        Setting::set('bank_channel_ippti', strtoupper($request->bank_channel_ippti));
        Setting::set('bank_account_ippti', preg_replace('/[^0-9]/', '', $request->bank_account_ippti));
        Setting::set('bank_holder_ippti', strtoupper($request->bank_holder_ippti));

        Setting::set('bank_name_benlaris', $request->bank_name_benlaris);
        Setting::set('bank_channel_benlaris', strtoupper($request->bank_channel_benlaris));
        Setting::set('bank_account_benlaris', preg_replace('/[^0-9]/', '', $request->bank_account_benlaris));
        Setting::set('bank_holder_benlaris', strtoupper($request->bank_holder_benlaris));

        Setting::set('auto_payout_monthly', $request->has('auto_payout_monthly') ? '1' : '0');
        Setting::set('min_payout_threshold', (string)$request->min_payout_threshold);

        AuditLog::log('UPDATE_FINANCE_BANK_SETTINGS', Setting::class, 0, [], $request->except('_token'));

        return back()->with('success', 'Pengaturan rekening bank IPPTI & Benlaris serta jadwal payout bulanan berhasil disimpan.');
    }

    /**
     * Trigger Manual 50:50 Split Disbursement
     */
    public function triggerDisbursement(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ], [
            'amount.required' => 'Nominal pencairan wajib diisi.',
            'amount.min' => 'Nominal pencairan minimal adalah Rp 10.000.',
        ]);

        $amount = (float)$request->amount;
        $result = $this->xenithService->executeSplitDisbursement($amount, 'manual', Auth::id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message'] ?? 'Gagal memproses pencairan ke rekening.');
        }
    }

    /**
     * Webhook Callback from Xenith Pay for Payout Transactions (/xenith/payout-callback)
     */
    public function handlePayoutCallback(Request $request)
    {
        // 1. Webhook Signature Verification
        $webhookSecret = Setting::get('xenith_webhook_secret') 
            ?: config('services.xenith.webhook_secret') 
            ?: env('XENITH_WEBHOOK_SECRET', 'tqYxuHTdCIRApkXloJviGV0l5aBcMSMhf8K05nvgFXfEMs7-Xw0D1lV79V_PJt3Q');

        $receivedSignature = trim((string)($request->header('X-Xenith-Signature') ?? $request->header('x-xenith-signature') ?? ''));
        $receivedTimestamp = trim((string)($request->header('X-Xenith-Timestamp') ?? $request->header('x-xenith-timestamp') ?? ''));

        if (!empty($webhookSecret) && !empty($receivedSignature) && !empty($receivedTimestamp)) {
            $method = strtoupper($request->method());
            $path = '/' . ltrim($request->path(), '/');
            $rawBody = $request->getContent();
            
            $stringToSign = $method . '\n' . $path . '\n' . $rawBody . '\n' . $receivedTimestamp;
            $computedSignature = base64_encode(hash_hmac('sha256', $stringToSign, $webhookSecret, true));

            if (!hash_equals($computedSignature, $receivedSignature)) {
                \Illuminate\Support\Facades\Log::warning('Xenith Payout Webhook Signature Mismatch', [
                    'computed' => $computedSignature,
                    'received' => $receivedSignature,
                    'path' => $path,
                    'timestamp' => $receivedTimestamp,
                ]);
                return response()->json(['status' => false, 'message' => 'Invalid Webhook Signature'], 401);
            }
        }

        // 2. Process payload
        $data = $request->input('data', []);
        $payoutId = $data['id'] ?? $request->input('id');
        $referenceCode = $data['referenceCode'] ?? $request->input('referenceCode');
        $status = strtolower((string)($data['status'] ?? $request->input('status', '')));

        if (!$referenceCode && !$payoutId) {
            return response()->json(['status' => false, 'message' => 'Missing reference or ID'], 400);
        }

        $payout = PayoutTransaction::where('reference_code', $referenceCode)
            ->orWhere('payout_id', $payoutId)
            ->first();

        if (!$payout) {
            return response()->json(['status' => false, 'message' => 'Payout transaction not found'], 404);
        }

        $normalizedStatus = match ($status) {
            'success', 'completed', 'paid' => 'success',
            'failed', 'rejected', 'cancelled' => 'failed',
            'processing', 'pending' => 'processing',
            default => 'processing',
        };

        $payout->update([
            'status' => $normalizedStatus,
            'fee_amount' => (float)($data['feeAmount'] ?? $payout->fee_amount),
            'raw_response' => json_encode($request->all()),
        ]);

        AuditLog::log('XENITH_PAYOUT_WEBHOOK_UPDATE', PayoutTransaction::class, $payout->id, [], [
            'status' => $normalizedStatus,
            'payout_id' => $payoutId,
        ]);

        return response()->json(['status' => true, 'message' => 'Payout status updated']);
    }
}
