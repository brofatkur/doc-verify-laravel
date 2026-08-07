<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentTransaction;
use App\Models\TopupOrder;
use App\Models\User;
use App\Models\Setting;
use App\Models\AuditLog;

class PaymentController extends Controller
{
    /**
     * Create Payment Session for Translator Self-Service Topup (Xenith Pay Sandbox / Production)
     */
    public function createPayment(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Silakan login terlebih dahulu.'], 401);
        }

        $minTopup = (float)Setting::get('min_topup_amount', 100000);

        $request->validate([
            'amount' => 'required|numeric|min:' . $minTopup,
        ], [
            'amount.required' => 'Nominal top-up wajib diisi.',
            'amount.numeric' => 'Nominal top-up harus berupa angka.',
            'amount.min' => 'Nominal top-up minimal Rp ' . number_format($minTopup, 0, ',', '.') . '.',
        ]);

        $amount = (float)$request->amount;
        $points = (int)$amount;

        $trxNo = 'TOPUP-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

        return $this->processGatewayCheckout($user, $trxNo, $amount, $points, 'Top-Up Poin Verifikasi Dokumen IPPTI');
    }

    /**
     * Create Payment Session for Pro Mode Upgrade Checkout (Same as Topup, 100% points allocated)
     */
    public function createProUpgradePayment(Request $request)
    {
        return $this->createPayment($request);
    }

    /**
     * Internal helper to build Xenith Pay payment link checkout request
     */
    private function processGatewayCheckout($user, $trxNo, $amount, $points, $productName, $orderType = 'topup')
    {
        // 1. Create topup order (Ledger Architecture)
        $topupOrder = TopupOrder::create([
            'order_id' => $trxNo,
            'user_id' => $user->id,
            'amount_idr' => $amount,
            'points_issued' => $points,
            'conversion_rate' => 1.00,
            'status' => 'pending',
            'payment_gateway' => 'xenith',
            'metadata' => json_encode(['order_type' => $orderType]),
        ]);

        // 2. Create payment transaction for legacy compatibility
        $transaction = PaymentTransaction::create([
            'transaction_no' => $trxNo,
            'user_id' => $user->id,
            'amount' => $amount,
            'points' => $points,
            'status' => 'pending',
        ]);

        // 3. Fetch Xenith Pay configuration
        $accessKey = config('services.xenith.access_key') ?: env('XENITH_ACCESS_KEY', 'ak-9ec9d28a3464154019f281404d6393b814bb0f14ad2981533999ad7cd22e1b88');
        $secretKey = config('services.xenith.secret_key') ?: env('XENITH_SECRET_KEY', 'sk-f5d8181853248796c878203d8a276a5bbb4be3a91d422b087dc8e142d2bbe6e9b048e381afd4cd91f2cddad9b785a1ac5503cf98bf70cc1609ccb4af6870656e');
        $env = config('services.xenith.env') ?: env('XENITH_ENV', 'sandbox');

        // Check if Xenith credentials are configured
        if (empty($accessKey) || empty($secretKey)) {
            $simulatedUrl = url('/xenith/return?trx_no=' . $trxNo . '&simulated=true');
            
            $transaction->update([
                'payment_url' => $simulatedUrl,
                'session_id' => 'SIMULATED-' . $trxNo,
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $simulatedUrl,
                'is_simulated' => true,
                'message' => 'Simulasi Pembayaran (Kredensial Xenith Pay belum dikonfigurasi).',
            ]);
        }

        $isProduction = strtolower($env) === 'production';
        $baseUrl = $isProduction ? 'https://openapi.xenithpay.com' : 'https://openapi.sandbox.xenithpay.com';
        $endpoint = '/v1/payment-links';
        $fullUrl = $baseUrl . $endpoint;

        $appUrl = url('/');
        $redirectUrl = $appUrl . '/xenith/return?trx_no=' . $trxNo;
        $callbackUrl = $appUrl . '/xenith/callback';

        $customerName = trim($user->name ?: 'Penerjemah IPPTI');
        if (strlen($customerName) < 5) {
            $customerName = $customerName . ' IPPTI';
        }

        $payload = [
            'amount' => (int)$amount,
            'currency' => 'IDR',
            'referenceCode' => $trxNo,
            'customerReference' => (string)$user->id,
            'customerName' => substr($customerName, 0, 50),
            'redirectUrl' => $redirectUrl,
            'paymentLinkCallbackUrl' => $callbackUrl,
            'payinCallbackUrl' => $callbackUrl,
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        
        $micro = microtime(true);
        $seconds = (int)$micro;
        $fraction = sprintf('%03d', ($micro - $seconds) * 1000);
        $timestamp = gmdate('Y-m-d\TH:i:s', $seconds) . '.' . $fraction . 'Z';
        
        // Build signature: METHOD + "\n" + URI + "\n" + TIMESTAMP + "\n" + BODY
        $signaturePayload = "POST\n" . $endpoint . "\n" . $timestamp . "\n" . $jsonBody;
        $signature = base64_encode(hash_hmac('sha256', $signaturePayload, trim($secretKey), true));

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Xenith-Api-Key' => trim($accessKey),
                'Xenith-Request-Timestamp' => $timestamp,
                'Xenith-Request-Signature' => $signature,
                'X-Idempotency-Key' => $trxNo,
            ])->withBody($jsonBody, 'application/json')->post($fullUrl);

            $resData = $response->json();

            $paymentUrl = $resData['data']['paymentLinkUrl'] 
                ?? $resData['data']['url'] 
                ?? $resData['data']['checkoutUrl'] 
                ?? $resData['paymentLinkUrl'] 
                ?? null;

            if ($response->successful() && !empty($paymentUrl)) {
                $sessionId = $resData['data']['id'] ?? $trxNo;

                $transaction->update([
                    'payment_url' => $paymentUrl,
                    'session_id' => $sessionId,
                ]);

                $topupOrder->update([
                    'metadata' => json_encode(array_merge(['order_type' => $orderType, 'gateway' => 'xenith'], $resData['data'] ?? [])),
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $paymentUrl,
                ]);
            } else {
                $code = $resData['code'] ?? '';
                $rawMsg = $resData['message'] ?? ($resData['error']['message'] ?? '');

                // Detect IP Whitelist restriction from Xenith Pay
                if ($code === 'UNKNOWN_IP_ADDRESS' || stripos($rawMsg, 'IP Address') !== false) {
                    $serverIp = request()->server('SERVER_ADDR') ?: (request()->ip() ?: 'IP Server');
                    $errMsg = "IP Address server ({$serverIp}) belum terdaftar di menu Developer Settings -> IP Whitelist di Dashboard Xenith Pay. Silakan daftarkan IP tersebut agar pembayaran otomatis terbuka.";
                } else {
                    $errMsg = !empty($rawMsg) ? $rawMsg : 'Gagal membuat tautan pembayaran Xenith Pay.';
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Respons Xenith Pay: ' . $errMsg,
                    'details' => $resData,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghubungkan ke Xenith Pay: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook Callback from Xenith Pay (/xenith/callback)
     * Handles idempotency & Pro Activation via point_transactions
     */
    public function handleXenithCallback(Request $request)
    {
        $data = $request->input('data', []);
        $refCode = $data['referenceCode'] ?? $request->input('referenceCode') ?? $request->input('reference_id');
        $status = strtoupper((string)($data['status'] ?? $request->input('status', '')));

        if (!$refCode) {
            return response()->json(['status' => false, 'message' => 'Reference Code missing'], 400);
        }

        $topupOrder = TopupOrder::where('order_id', $refCode)->first();
        $transaction = PaymentTransaction::where('transaction_no', $refCode)->first();

        if (!$topupOrder && !$transaction) {
            return response()->json(['status' => false, 'message' => 'Transaction order not found'], 404);
        }

        $userId = $topupOrder ? $topupOrder->user_id : $transaction->user_id;
        $points = $topupOrder ? $topupOrder->points_issued : $transaction->points;

        $isSuccess = in_array($status, ['COMPLETED', 'SUCCESS', 'PAID']);
        $channel = $data['paymentChannel'] ?? $data['paymentMethod'] ?? 'Xenith Pay';
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
                    'payment_method' => $channel,
                ]);
            }

            $user = User::find($userId);
            if ($user) {
                // Auto-upgrade account to PRO if user is currently reguler
                if ($user->isReguler()) {
                    $user->update(['user_level' => 'pro']);
                }

                $idempotencyKey = 'topup_order_' . $refCode;
                $user->creditPoints(
                    $points,
                    'Topup Poin via Xenith Pay (' . $refCode . ')',
                    'topup',
                    $refCode,
                    $idempotencyKey,
                    ['channel' => $channel, 'raw_response' => $request->all()]
                );

                AuditLog::log(
                    'XENITH_TOPUP_PAID',
                    TopupOrder::class,
                    $topupOrder ? $topupOrder->id : $transaction->id,
                    [],
                    ['points' => $user->points, 'added' => $points, 'user_level' => $user->user_level, 'trx_no' => $refCode]
                );
            }

            return response()->json(['status' => true, 'message' => 'Payment successfully credited']);
        } else {
            if ($topupOrder) {
                $topupOrder->update(['status' => strtolower($status) ?: 'failed', 'payment_response_text' => $rawPayload]);
            }
            if ($transaction) {
                $transaction->update(['status' => strtolower($status) ?: 'failed']);
            }
            return response()->json(['status' => true, 'message' => 'Transaction status updated to ' . $status]);
        }
    }

    /**
     * Legacy Callback for iPaymu (/ipaymu/callback)
     */
    public function handleCallback(Request $request)
    {
        return $this->handleXenithCallback($request);
    }

    /**
     * Return Page after payment (/xenith/return)
     */
    public function handleXenithReturn(Request $request)
    {
        $trxNo = $request->input('trx_no') ?? $request->input('referenceCode') ?? $request->input('reference_id');
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

        // If simulation mode and pending, confirm simulated payment for testing
        if ($isSimulated && $transaction && strtolower($transaction->status) === 'pending') {
            $user = User::find($transaction->user_id);
            if ($user) {
                if ($user->isReguler()) {
                    $user->update(['user_level' => 'pro']);
                }

                $idempotencyKey = 'topup_order_' . $trxNo;
                $user->creditPoints(
                    $transaction->points,
                    'Simulasi Topup Poin via Xenith Pay (' . $trxNo . ')',
                    'topup',
                    $trxNo,
                    $idempotencyKey,
                    ['simulated' => true]
                );

                AuditLog::log(
                    'SIMULATED_XENITH_TOPUP_PAID',
                    PaymentTransaction::class,
                    $transaction->id ?? 0,
                    [],
                    ['added_points' => $transaction->points, 'user_level' => $user->user_level, 'trx_no' => $trxNo]
                );

                if (method_exists($transaction, 'update')) {
                    $transaction->update(['status' => 'paid', 'payment_method' => 'SIMULATED']);
                }
                
                TopupOrder::where('order_id', $trxNo)->update(['status' => 'success', 'payment_channel' => 'SIMULATED']);
            }
        }

        return view('payment.return', compact('transaction', 'isSimulated'));
    }

    /**
     * Legacy Return Page for iPaymu (/ipaymu/return)
     */
    public function handleReturn(Request $request)
    {
        return $this->handleXenithReturn($request);
    }
}
