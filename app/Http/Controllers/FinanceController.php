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

        // Live Xenith Balances
        $balanceData = $this->xenithService->getBalances();

        // Inflow (Top-Up Masuk)
        $totalInflow = (float)TopupOrder::where('status', 'success')->sum('amount_idr');
        $totalPointsIssued = (int)TopupOrder::where('status', 'success')->sum('points');
        $totalPayinCount = TopupOrder::where('status', 'success')->count();
        $thisMonthInflow = (float)TopupOrder::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_idr');

        // Outflow (Pencairan / Payout)
        $totalPayoutDisbursed = (float)PayoutTransaction::whereIn('status', ['success', 'simulated'])->sum('amount');
        $totalPayoutIppti = (float)PayoutTransaction::where('recipient_type', 'IPPTI')
            ->whereIn('status', ['success', 'simulated'])->sum('amount');
        $totalPayoutBenlaris = (float)PayoutTransaction::where('recipient_type', 'BENLARIS')
            ->whereIn('status', ['success', 'simulated'])->sum('amount');
        $pendingPayoutCount = PayoutTransaction::whereIn('status', ['pending', 'processing'])->count();

        // Available Balance to Disburse
        $systemAvailable = max(0, $totalInflow - $totalPayoutDisbursed);
        $liveAvailable = (float)($balanceData['available_balance'] ?? 0);
        $readyToDisburse = $liveAvailable > 0 ? $liveAvailable : $systemAvailable;

        $splitIppti = floor($readyToDisburse / 2);
        $splitBenlaris = floor($readyToDisburse / 2);

        // Bank Accounts & Settings
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

        // Transactions History
        $payinOrders = TopupOrder::with('user')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'payin_page');

        $payoutTransactions = PayoutTransaction::with('user')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'payout_page');

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
            'bankSettings',
            'payinOrders',
            'payoutTransactions'
        ));
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
