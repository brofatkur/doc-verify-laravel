<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentTransaction;
use App\Models\TopupOrder;
use App\Models\User;
use App\Models\AuditLog;

class PaymentController extends Controller
{
    /**
     * Create iPaymu Payment Session for Translator Self-Service Topup
     */
    public function createPayment(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Silakan login terlebih dahulu.'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ], [
            'amount.required' => 'Nominal top-up wajib diisi.',
            'amount.numeric' => 'Nominal top-up harus berupa angka.',
            'amount.min' => 'Nominal top-up minimal Rp 10.000.',
        ]);

        $amount = (float)$request->amount;
        $points = (int)$amount;

        $trxNo = 'TRX-IPAYMU-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

        // Create topup order (New Ledger Architecture)
        $topupOrder = TopupOrder::create([
            'order_id' => $trxNo,
            'user_id' => $user->id,
            'amount_idr' => $amount,
            'points_issued' => $points,
            'conversion_rate' => 1.00,
            'status' => 'pending',
            'payment_gateway' => 'ipaymu',
        ]);

        // Create legacy payment transaction for compatibility
        $transaction = PaymentTransaction::create([
            'transaction_no' => $trxNo,
            'user_id' => $user->id,
            'amount' => $amount,
            'points' => $points,
            'status' => 'pending',
        ]);

        // Fetch iPaymu configuration
        $va = config('services.ipaymu.va') ?: env('IPAYMU_VA', '');
        $apiKey = config('services.ipaymu.api_key') ?: env('IPAYMU_API_KEY', '');
        $env = config('services.ipaymu.env') ?: env('IPAYMU_ENV', 'sandbox');

        // Check if iPaymu credentials are configured
        if (empty($va) || empty($apiKey)) {
            // Fallback for simulation / testing when credentials are not yet set in .env
            $simulatedUrl = url('/ipaymu/return?trx_no=' . $trxNo . '&simulated=true');
            
            $transaction->update([
                'payment_url' => $simulatedUrl,
                'session_id' => 'SIMULATED-' . $trxNo,
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $simulatedUrl,
                'is_simulated' => true,
                'message' => 'Simulasi Pembayaran (Kredensial iPaymu belum dikonfigurasi di admin).',
            ]);
        }

        $baseUrl = strtolower($env) === 'production'
            ? 'https://api.ipaymu.com/api/v2/payment'
            : 'https://sandbox.ipaymu.com/api/v2/payment';

        $appUrl = url('/');
        $returnUrl = $appUrl . '/ipaymu/return?trx_no=' . $trxNo;
        $notifyUrl = $appUrl . '/ipaymu/callback';
        $cancelUrl = $appUrl . '/admin';

        $body = [
            'product' => ['Top-Up Poin Verifikasi Dokumen IPPTI (' . number_format($points, 0, ',', '.') . ' Poin)'],
            'qty' => [1],
            'price' => [$amount],
            'amount' => $amount,
            'returnUrl' => $returnUrl,
            'notifyUrl' => $notifyUrl,
            'cancelUrl' => $cancelUrl,
            'referenceId' => $trxNo,
            'buyerName' => $user->name,
            'buyerEmail' => $user->email,
            'buyerPhone' => $user->whatsapp ?: '08123456789',
        ];

        $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . trim($va) . ':' . $bodyHash . ':' . trim($apiKey);
        $signature = hash_hmac('sha256', $stringToSign, trim($apiKey));

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'va' => trim($va),
                'signature' => $signature,
                'timestamp' => date('YmdHis'),
            ])->post($baseUrl, $body);

            $resData = $response->json();

            if ($response->successful() && isset($resData['Data']['Url'])) {
                $paymentUrl = $resData['Data']['Url'];
                $sessionId = $resData['Data']['SessionID'] ?? null;

                $transaction->update([
                    'payment_url' => $paymentUrl,
                    'session_id' => $sessionId,
                    'ipaymu_trx_id' => $resData['Data']['TransactionId'] ?? null,
                ]);

                $topupOrder->update([
                    'metadata' => json_encode($resData['Data'] ?? []),
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $paymentUrl,
                ]);
            } else {
                $errMsg = $resData['Message'] ?? ($resData['message'] ?? 'Gagal menghubungi server iPaymu.');
                return response()->json([
                    'success' => false,
                    'error' => 'Respons iPaymu: ' . $errMsg,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghubungkan ke iPaymu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook Callback from iPaymu (/ipaymu/callback)
     * Handles idempotency via point_transactions idempotency_key
     */
    public function handleCallback(Request $request)
    {
        $trxNo = $request->input('reference_id') ?? $request->input('referenceId');
        $status = strtolower((string)($request->input('status') ?? $request->input('trx_status') ?? ''));
        $statusNo = $request->input('status_code') ?? $request->input('status');

        if (!$trxNo) {
            return response()->json(['status' => false, 'message' => 'Reference ID missing'], 400);
        }

        $topupOrder = TopupOrder::where('order_id', $trxNo)->first();
        $transaction = PaymentTransaction::where('transaction_no', $trxNo)->first();

        if (!$topupOrder && !$transaction) {
            return response()->json(['status' => false, 'message' => 'Transaction order not found'], 404);
        }

        $userId = $topupOrder ? $topupOrder->user_id : $transaction->user_id;
        $amount = $topupOrder ? $topupOrder->amount_idr : $transaction->amount;
        $points = $topupOrder ? $topupOrder->points_issued : $transaction->points;

        $isSuccess = in_array($status, ['berhasil', 'paid', 'success', 'settlement']) || $statusNo == 200 || $statusNo == 1;

        $channel = $request->input('via') ?? $request->input('channel') ?? 'iPaymu';
        $rawPayload = json_encode($request->all());

        if ($isSuccess) {
            if ($topupOrder) {
                $topupOrder->update([
                    'status' => 'success',
                    'payment_channel' => $channel,
                    'payment_response_text' => $rawPayload,
                ]);
            }
            if ($transaction) {
                $transaction->update([
                    'status' => 'paid',
                    'ipaymu_trx_id' => $request->input('trx_id') ?? $transaction->ipaymu_trx_id,
                    'payment_method' => $channel,
                ]);
            }

            // Credit points via point_transactions ledger with Idempotency Key
            $user = User::find($userId);
            if ($user) {
                $idempotencyKey = 'topup_order_' . $trxNo;
                $user->creditPoints(
                    $points,
                    'Topup Poin via iPaymu (' . $trxNo . ')',
                    'topup',
                    $trxNo,
                    $idempotencyKey,
                    ['channel' => $channel, 'raw_response' => $request->all()]
                );

                AuditLog::log(
                    'IPAYMU_TOPUP_PAID',
                    TopupOrder::class,
                    $topupOrder ? $topupOrder->id : $transaction->id,
                    [],
                    ['points' => $user->points, 'added' => $points, 'trx_no' => $trxNo]
                );
            }

            return response()->json(['status' => true, 'message' => 'Payment successfully credited']);
        } else {
            if ($topupOrder) {
                $topupOrder->update(['status' => 'failed', 'payment_response_text' => $rawPayload]);
            }
            if ($transaction) {
                $transaction->update(['status' => 'failed']);
            }
            return response()->json(['status' => true, 'message' => 'Transaction marked as failed']);
        }
    }

    /**
     * Return Page after payment (/ipaymu/return)
     */
    public function handleReturn(Request $request)
    {
        $trxNo = $request->input('trx_no') ?? $request->input('reference_id');
        $isSimulated = $request->boolean('simulated');

        $transaction = null;
        if ($trxNo) {
            $transaction = PaymentTransaction::where('transaction_no', $trxNo)->first();
            if (!$transaction) {
                $topupOrder = TopupOrder::where('order_id', $trxNo)->first();
                if ($topupOrder) {
                    $transaction = (object)[
                        'transaction_no' => $topupOrder->order_id,
                        'user_id' => $topupOrder->user_id,
                        'amount' => $topupOrder->amount_idr,
                        'points' => $topupOrder->points_issued,
                        'status' => $topupOrder->status === 'success' ? 'paid' : $topupOrder->status,
                        'updated_at' => $topupOrder->updated_at,
                    ];
                }
            }
        }

        // If simulation mode and pending, confirm simulated top-up for testing
        if ($isSimulated && $transaction && strtolower($transaction->status) === 'pending') {
            $user = User::find($transaction->user_id);
            if ($user) {
                $idempotencyKey = 'topup_order_' . $trxNo;
                $user->creditPoints(
                    $transaction->points,
                    'Simulasi Topup Poin via iPaymu (' . $trxNo . ')',
                    'topup',
                    $trxNo,
                    $idempotencyKey,
                    ['simulated' => true]
                );

                if (method_exists($transaction, 'update')) {
                    $transaction->update(['status' => 'paid', 'payment_method' => 'SIMULATED']);
                }
                
                TopupOrder::where('order_id', $trxNo)->update(['status' => 'success', 'payment_channel' => 'SIMULATED']);

                AuditLog::log(
                    'SIMULATED_TOPUP_PAID',
                    PaymentTransaction::class,
                    $transaction->id ?? 0,
                    [],
                    ['added_points' => $transaction->points, 'trx_no' => $trxNo]
                );
            }
        }

        return view('payment.return', compact('transaction', 'isSimulated'));
    }
}
