<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\PayoutTransaction;
use App\Models\Setting;
use App\Models\AuditLog;

class XenithPayService
{
    protected string $accessKey;
    protected string $secretKey;
    protected string $env;
    protected string $baseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->accessKey = trim((string)(Setting::get('xenith_access_key') ?: config('services.xenith.access_key') ?: env('XENITH_ACCESS_KEY', 'ak-6b4c740dae79be46ee0189169e6636fd41917774f4365a4025529300c731bd2b')));
        $this->secretKey = trim((string)(Setting::get('xenith_secret_key') ?: config('services.xenith.secret_key') ?: env('XENITH_SECRET_KEY', 'sk-2cc25d1dd0b624c03650712be4749eea1686ce4fb30690a6316dee7ef96879896f9a19fc211c9e7fd429771f888a422099f01e9bc34c0fbeac4b07ab9d9f799e')));
        $this->env = strtolower((string)(Setting::get('xenith_env') ?: config('services.xenith.env') ?: env('XENITH_ENV', 'production')));
        $this->isProduction = $this->env === 'production';
        $this->baseUrl = $this->isProduction ? 'https://openapi.xenithpay.com' : 'https://openapi.sandbox.xenithpay.com';
    }

    /**
     * Generate ISO 8601 UTC Timestamp with Milliseconds
     */
    protected function generateTimestamp(): string
    {
        $micro = microtime(true);
        $seconds = (int)$micro;
        $fraction = sprintf('%03d', ($micro - $seconds) * 1000);
        return gmdate('Y-m-d\TH:i:s', $seconds) . '.' . $fraction . 'Z';
    }

    /**
     * Generate HMAC SHA256 Request Signature
     * Format: METHOD + "\n" + URI + "\n" + TIMESTAMP + "\n" + JSON_BODY
     */
    protected function generateSignature(string $method, string $uri, string $timestamp, string $body = ''): string
    {
        $signaturePayload = strtoupper($method) . "\n" . $uri . "\n" . $timestamp . "\n" . $body;
        return base64_encode(hash_hmac('sha256', $signaturePayload, $this->secretKey, true));
    }

    /**
     * Get Merchant Balances from Xenith Pay (GET /v1/balances)
     */
    public function getBalances(): array
    {
        if (empty($this->accessKey) || empty($this->secretKey)) {
            return [
                'success' => false,
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_balance' => 0,
                'currency' => 'IDR',
                'is_simulated' => true,
                'message' => 'Kredensial API Xenith Pay belum dikonfigurasi.',
            ];
        }

        $endpoint = '/v1/balances';
        $fullUrl = $this->baseUrl . $endpoint;
        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature('GET', $endpoint, $timestamp, '');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Xenith-Api-Key' => $this->accessKey,
                'Xenith-Request-Timestamp' => $timestamp,
                'Xenith-Request-Signature' => $signature,
            ])->timeout(10)->get($fullUrl);

            $resData = $response->json();

            if ($response->successful() && !empty($resData['data'])) {
                $item = is_array($resData['data']) && isset($resData['data'][0]) 
                    ? $resData['data'][0] 
                    : (is_array($resData['data']) ? $resData['data'] : []);
                
                $avail = (float)($item['availableBalance'] ?? $item['available_balance'] ?? $item['balance'] ?? 0);
                $pending = (float)($item['pendingBalance'] ?? $item['pending_balance'] ?? 0);
                $total = (float)($item['totalBalance'] ?? $item['total_balance'] ?? ($avail + $pending));

                return [
                    'success' => true,
                    'available_balance' => $avail,
                    'pending_balance' => $pending,
                    'total_balance' => $total,
                    'currency' => $item['currency'] ?? 'IDR',
                    'raw' => $resData,
                ];
            }

            return [
                'success' => false,
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_balance' => 0,
                'currency' => 'IDR',
                'error' => $resData['message'] ?? 'Gagal memuat saldo dari Xenith Pay.',
                'raw' => $resData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_balance' => 0,
                'currency' => 'IDR',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Pay-In List directly from Xenith Pay API (GET /v1/payins)
     */
    public function getPayInsList(int $page = 1, int $limit = 20): array
    {
        if (empty($this->accessKey) || empty($this->secretKey)) {
            return ['success' => false, 'data' => []];
        }

        $endpoint = '/v1/payins';
        $fullUrl = $this->baseUrl . $endpoint . '?page=' . $page . '&limit=' . $limit;
        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature('GET', $endpoint, $timestamp, '');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Xenith-Api-Key' => $this->accessKey,
                'Xenith-Request-Timestamp' => $timestamp,
                'Xenith-Request-Signature' => $signature,
            ])->timeout(10)->get($fullUrl);

            $resData = $response->json();
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $resData['data'] ?? [],
                    'meta' => $resData['meta'] ?? $resData['pagination'] ?? [],
                ];
            }

            return ['success' => false, 'error' => $resData['message'] ?? 'Failed to fetch payins', 'data' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Create Payout Transaction via Xenith Pay (POST /v1/payouts)
     */
    public function createPayout(
        float $initiatedAmount,
        string $recipientType,
        string $bankChannel,
        string $bankAccount,
        string $accountName,
        string $description,
        string $triggerType = 'manual',
        ?int $userId = null
    ): array {
        $referenceCode = 'PO-' . strtoupper($recipientType) . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
        $idempotencyKey = 'payout_' . $referenceCode;

        $payoutRecord = PayoutTransaction::create([
            'reference_code' => $referenceCode,
            'recipient_type' => $recipientType,
            'amount' => $initiatedAmount,
            'fee_amount' => 2500, // standard Xenith payout bank fee
            'bank_name' => Setting::get('bank_name_' . strtolower($recipientType), 'BCA'),
            'bank_channel' => $bankChannel ?: 'CENAIDJA',
            'account_number' => $bankAccount,
            'account_holder_name' => $accountName,
            'status' => 'pending',
            'trigger_type' => $triggerType,
            'created_by' => $userId,
        ]);

        $endpoint = '/v1/payouts';
        $fullUrl = $this->baseUrl . $endpoint;
        $timestamp = $this->generateTimestamp();

        $appUrl = config('app.url') ?: url('/');
        $callbackUrl = $appUrl . '/xenith/payout-callback';

        $payload = [
            'initiatedAmount' => (int)$initiatedAmount,
            'currency' => 'IDR',
            'destinationPayoutMethod' => 'BANK_TRANSFER',
            'destinationPayoutChannel' => $bankChannel ?: 'CENAIDJA',
            'destinationPayoutAccount' => (string)$bankAccount,
            'destinationPayoutAccountName' => (string)$accountName,
            'referenceCode' => $referenceCode,
            'customerReference' => 'CUST-' . $recipientType . '-01',
            'description' => substr($description, 0, 100),
            'callbackUrl' => $callbackUrl,
            'metadata' => [
                'recipient_type' => $recipientType,
                'trigger_type' => $triggerType,
            ]
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = $this->generateSignature('POST', $endpoint, $timestamp, $jsonBody);

        $payoutRecord->update(['raw_request' => $jsonBody]);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Xenith-Api-Key' => $this->accessKey,
                'Xenith-Request-Timestamp' => $timestamp,
                'Xenith-Request-Signature' => $signature,
                'X-Idempotency-Key' => $idempotencyKey,
                'Xenith-Signature-Version' => 'V2',
            ])->withBody($jsonBody, 'application/json')->timeout(15)->post($fullUrl);

            $resData = $response->json();
            $rawResponse = json_encode($resData);

            if ($response->successful() && isset($resData['id'])) {
                $payoutRecord->update([
                    'payout_id' => $resData['id'],
                    'status' => strtolower($resData['status'] ?? 'processing'),
                    'fee_amount' => (float)($resData['feeAmount'] ?? 2500),
                    'raw_response' => $rawResponse,
                ]);

                AuditLog::log(
                    'XENITH_PAYOUT_SUCCESS',
                    PayoutTransaction::class,
                    $payoutRecord->id,
                    [],
                    ['amount' => $initiatedAmount, 'recipient' => $recipientType, 'reference' => $referenceCode]
                );

                return [
                    'success' => true,
                    'payout_id' => $resData['id'],
                    'reference_code' => $referenceCode,
                    'status' => $resData['status'] ?? 'PROCESSING',
                    'message' => 'Pencairan dana Rp ' . number_format($initiatedAmount, 0, ',', '.') . ' ke ' . $recipientType . ' berhasil diajukan ke Xenith Pay.',
                    'data' => $resData,
                ];
            } else {
                $status = !$this->isProduction ? 'simulated' : 'failed';
                $payoutRecord->update([
                    'status' => $status,
                    'raw_response' => $rawResponse,
                ]);

                $errMsg = $resData['message'] ?? 'Gagal membuat pencairan dana ke Xenith Pay.';

                return [
                    'success' => !$this->isProduction,
                    'simulated' => !$this->isProduction,
                    'reference_code' => $referenceCode,
                    'status' => $status,
                    'message' => !$this->isProduction
                        ? 'Pencairan simulasi sandbox Rp ' . number_format($initiatedAmount, 0, ',', '.') . ' ke ' . $recipientType . ' berhasil dicatat.'
                        : 'Respons Xenith: ' . $errMsg,
                    'error' => $errMsg,
                ];
            }
        } catch (\Exception $e) {
            $status = !$this->isProduction ? 'simulated' : 'failed';
            $payoutRecord->update([
                'status' => $status,
                'raw_response' => $e->getMessage(),
            ]);

            return [
                'success' => !$this->isProduction,
                'simulated' => !$this->isProduction,
                'reference_code' => $referenceCode,
                'status' => $status,
                'message' => !$this->isProduction
                    ? 'Pencairan simulasi testing Rp ' . number_format($initiatedAmount, 0, ',', '.') . ' ke ' . $recipientType . ' berhasil dicatat.'
                    : 'Gagal menghubungkan ke gateway: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute 50:50 Disbursement Split between IPPTI and Benlaris
     */
    public function executeSplitDisbursement(float $totalAmount, string $triggerType = 'manual', ?int $userId = null): array
    {
        if ($totalAmount < 10000) {
            return [
                'success' => false,
                'message' => 'Nominal pencairan minimal adalah Rp 10.000.',
            ];
        }

        $halfAmount = floor($totalAmount / 2);

        // 1. IPPTI Account Details
        $ipptiBank = Setting::get('bank_name_ippti', 'BCA');
        $ipptiChannel = Setting::get('bank_channel_ippti', 'CENAIDJA');
        $ipptiAccount = Setting::get('bank_account_ippti', '1234567890');
        $ipptiHolder = Setting::get('bank_holder_ippti', 'IKATAN PENERJEMAH INDONESIA');

        // 2. Benlaris Account Details
        $benlarisBank = Setting::get('bank_name_benlaris', 'Mandiri');
        $benlarisChannel = Setting::get('bank_channel_benlaris', 'BMRIIDJA');
        $benlarisAccount = Setting::get('bank_account_benlaris', '0987654321');
        $benlarisHolder = Setting::get('bank_holder_benlaris', 'PT BENLARIS SUKSES INDONESIA');

        // Execute Payout 1 (IPPTI 50%)
        $resIppti = $this->createPayout(
            $halfAmount,
            'IPPTI',
            $ipptiChannel,
            $ipptiAccount,
            $ipptiHolder,
            'Bagi Hasil 50% IPPTI - DocVerify',
            $triggerType,
            $userId
        );

        // Execute Payout 2 (Benlaris 50%)
        $resBenlaris = $this->createPayout(
            $halfAmount,
            'BENLARIS',
            $benlarisChannel,
            $benlarisAccount,
            $benlarisHolder,
            'Bagi Hasil 50% Benlaris - DocVerify',
            $triggerType,
            $userId
        );

        return [
            'success' => $resIppti['success'] && $resBenlaris['success'],
            'total_amount' => $totalAmount,
            'split_amount' => $halfAmount,
            'ippti' => $resIppti,
            'benlaris' => $resBenlaris,
            'message' => 'Pencairan 50% IPPTI (Rp ' . number_format($halfAmount, 0, ',', '.') . ') dan 50% Benlaris (Rp ' . number_format($halfAmount, 0, ',', '.') . ') telah diproses.',
        ];
    }
}
