<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\XenithPayService;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\TopupOrder;
use App\Models\PayoutTransaction;

class MonthlyPayoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:monthly-payout {--force : Paksa eksekusi tanpa mengecek status toggle jadwal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksekusi pencairan otomatis bagi hasil 50% IPPTI & 50% Benlaris pada tiap awal bulan via Xenith Pay';

    /**
     * Execute the console command.
     */
    public function handle(XenithPayService $xenithService)
    {
        $this->info('Memulai pengecekan pencairan otomatis awal bulan (50% IPPTI & 50% Benlaris)...');

        $isAutoEnabled = Setting::get('auto_payout_monthly', '1') === '1';
        $isForce = $this->option('force');

        if (!$isAutoEnabled && !$isForce) {
            $this->warn('Fitur pencairan otomatis awal bulan dinonaktifkan di pengaturan sistem.');
            return 0;
        }

        $minThreshold = (float)Setting::get('min_payout_threshold', 100000);

        // Calculate available earnings from payin topups minus already disbursed payouts
        $totalInflow = (float)TopupOrder::where('status', 'success')->sum('amount_idr');
        $totalOutflow = (float)PayoutTransaction::whereIn('status', ['success', 'processing', 'simulated'])->sum('amount');
        $availableCalculated = max(0, $totalInflow - $totalOutflow);

        // Fetch live balances from Xenith Pay API
        $balanceData = $xenithService->getBalances();
        $xenithAvailable = (float)($balanceData['available_balance'] ?? 0);

        $amountToDisburse = $xenithAvailable > 0 ? $xenithAvailable : $availableCalculated;

        $this->line('Total Inflow: Rp ' . number_format($totalInflow, 0, ',', '.'));
        $this->line('Total Sudah Dicairkan: Rp ' . number_format($totalOutflow, 0, ',', '.'));
        $this->line('Saldo Siap Cair: Rp ' . number_format($amountToDisburse, 0, ',', '.'));

        if ($amountToDisburse < $minThreshold) {
            $this->warn("Saldo siap cair (Rp " . number_format($amountToDisburse, 0, ',', '.') . ") belum mencapai batas minimal pencairan (Rp " . number_format($minThreshold, 0, ',', '.') . ").");
            return 0;
        }

        $this->info("Mengeksekusi pencairan bagi hasil 50:50 sebesar Rp " . number_format($amountToDisburse, 0, ',', '.') . "...");

        $result = $xenithService->executeSplitDisbursement($amountToDisburse, 'auto_monthly', null);

        if ($result['success']) {
            $this->info('Pencairan otomatis awal bulan berhasil!');
            $this->info('IPPTI 50%: Rp ' . number_format($result['split_amount'], 0, ',', '.'));
            $this->info('Benlaris 50%: Rp ' . number_format($result['split_amount'], 0, ',', '.'));

            AuditLog::log('AUTO_MONTHLY_PAYOUT_SUCCESS', PayoutTransaction::class, 0, [], [
                'total_amount' => $amountToDisburse,
                'split_amount' => $result['split_amount'],
                'trigger' => 'auto_monthly',
            ]);
        } else {
            $this->error('Pencairan otomatis gagal: ' . ($result['message'] ?? 'Terjadi kesalahan'));
        }

        return 0;
    }
}
