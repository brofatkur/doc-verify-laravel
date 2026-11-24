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
    protected $description = 'Kirim pengingat perpanjangan keanggotaan tahunan IPPTI (H-30, H-10, & Expired) via Email & WhatsApp (Kirimdev.com)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $d1 = max(1, (int)Setting::get('member_reminder_days_1', 30));
        $d2 = max(1, (int)Setting::get('member_reminder_days_2', 10));
        $maxReminderDays = max($d1, $d2);
        $force = (bool)$this->option('force');

        $translators = User::where('role', 'TRANSLATOR')->get();
        $sentCount = 0;

        foreach ($translators as $user) {
            if (!$user->isVerifiedTranslator()) {
                continue;
            }
            if ($user->isExpiringSoon($maxReminderDays) || $user->isMembershipExpired()) {
                $res = $user->sendExpiryReminder($force);
                if (!empty($res['sent'])) {
                    $sentCount++;
                    $channels = [];
                    if (!empty($res['email_sent'])) $channels[] = "Email ({$user->email})";
                    if (!empty($res['whatsapp_sent'])) $channels[] = "WhatsApp Kirimdev ({$user->whatsapp})";
                    $this->info("Reminder [{$res['stage']}] terkirim ke: {$user->name} via " . implode(' & ', $channels));
                }
            }
        }

        $this->info("Selesai. Total member yang dikirimi pengingat perpanjangan (Email & WA Kirimdev.com): {$sentCount}");
        return Command::SUCCESS;
    }
}
