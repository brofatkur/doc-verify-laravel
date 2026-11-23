<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;

class SendMemberExpiryRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:send-expiry-reminders {--force : Kirim ulang meskipun sudah dikirim hari ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat perpanjangan keanggotaan tahunan IPPTI kepada member yang akan atau sudah expired';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reminderDays = (int)Setting::get('member_expiry_reminder_days', 30);
        $force = (bool)$this->option('force');

        $translators = User::where('role', 'TRANSLATOR')->get();
        $sentCount = 0;

        foreach ($translators as $user) {
            if (!$user->isVerifiedTranslator()) {
                continue;
            }
            if ($user->isExpiringSoon($reminderDays) || $user->isMembershipExpired()) {
                if ($user->sendExpiryReminderEmail($force)) {
                    $sentCount++;
                    $this->info("Reminder terkirim ke: {$user->name} ({$user->email})");
                }
            }
        }

        $this->info("Selesai. Total email pengingat perpanjangan terkirim: {$sentCount}");
        return Command::SUCCESS;
    }
}
