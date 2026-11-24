<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Document;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $isSuperAdmin = $user->role === 'SUPERADMIN';
        $isAdmin = $user->role === 'ADMIN';
        $d1 = max(1, (int)\App\Models\Setting::get('member_reminder_days_1', 30));
        $d2 = max(1, (int)\App\Models\Setting::get('member_reminder_days_2', 10));
        $reminderDays = max($d1, $d2);

        // Auto-dispatch H-30 & H-10 reminders (Email + WhatsApp Kirimdev.com) across all members
        User::dispatchAutomaticExpiryReminders(false);

        if ($isSuperAdmin || $isAdmin) {
            $translators = User::withCount('documents')->orderBy('name', 'asc')->get();
            $documents = Document::with('translator')->orderBy('created_at', 'desc')->get();

            $totalTranslators = $translators->count();
            $totalDocs = $documents->count();
            $totalQrCodes = $documents->where('is_qr_generated', true)->count();

            // Document type distribution statistics (REV-10)
            $docTypeStats = Document::groupBy('document_type')
                ->selectRaw('document_type, count(*) as count')
                ->orderBy('count', 'desc')
                ->get();

            return view('admin.dashboard', compact(
                'isSuperAdmin', 'isAdmin', 'translators', 'documents', 
                'totalTranslators', 'totalDocs', 'totalQrCodes', 'docTypeStats'
            ));
        }

        // If translator clicked the renewal payment link from their reminder email / WhatsApp (?pay_renewal=1)
        if ($request->boolean('pay_renewal')) {
            $paymentUrl = app(\App\Http\Controllers\PaymentController::class)->generateMembershipPaymentUrl($user, 'renewal');
            if (!empty($paymentUrl)) {
                return redirect()->away($paymentUrl);
            }
        }

        // Auto-send Email & WhatsApp reminder for the current translator if at H-30, H-10, or Expired
        if ($user->isExpiringSoon($reminderDays) || $user->isMembershipExpired()) {
            $user->sendExpiryReminder(false);
        }

        // Sworn Translator Dashboard view
        $documents = Document::where('translator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalDocs = $documents->count();
        $totalQrCodes = $documents->where('is_qr_generated', true)->count();

        return view('admin.dashboard', compact('isSuperAdmin', 'isAdmin', 'documents', 'totalDocs', 'totalQrCodes'));
    }

    public function createDocument()
    {
        $user = Auth::user();
        if ($user && !$user->isVerifiedTranslator()) {
            return redirect('/admin')->withErrors([
                'verification' => 'Akun Anda sedang menunggu verifikasi Pengurus Admin IPPTI. Fitur input data dokumen baru dapat digunakan setelah akun Anda diverifikasi.'
            ]);
        }
        if ($user && $user->isMembershipExpired()) {
            return redirect('/admin')->withErrors([
                'membership' => 'Masa aktif keanggotaan tahunan IPPTI Anda telah berakhir (Expired). Silakan lakukan perpanjangan keanggotaan terlebih dahulu.'
            ]);
        }

        $documentTypes = \App\Models\DocumentType::orderBy('name', 'asc')->get();
        $languageDirections = \App\Models\LanguageDirection::orderBy('name', 'asc')->get();
        return view('admin.new', compact('documentTypes', 'languageDirections'));
    }

    public function editDocument($id)
    {
        $user = Auth::user();
        if ($user && !$user->isVerifiedTranslator()) {
            return redirect('/admin')->withErrors([
                'verification' => 'Akun Anda belum diverifikasi oleh Pengurus Admin IPPTI.'
            ]);
        }
        if ($user && $user->isMembershipExpired()) {
            return redirect('/admin')->withErrors([
                'membership' => 'Masa aktif keanggotaan tahunan IPPTI Anda telah berakhir (Expired). Silakan lakukan perpanjangan keanggotaan terlebih dahulu.'
            ]);
        }

        $document = Document::findOrFail($id);

        if ($document->translator_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda bukan pemilik dokumen ini.');
        }

        $documentTypes = \App\Models\DocumentType::orderBy('name', 'asc')->get();
        $languageDirections = \App\Models\LanguageDirection::orderBy('name', 'asc')->get();

        return view('admin.edit', compact('document', 'documentTypes', 'languageDirections'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    // User management actions for Admin/Super Admin
    public function users()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            abort(403, 'Akses ditolak.');
        }

        $translators = User::withCount('documents')->orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('translators'));
    }

    public function storeUser(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $request->validate([
            'role' => 'required|in:TRANSLATOR,ADMIN,SUPERADMIN',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'sk_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $role = $request->role;
        if ($currentUser->role === 'ADMIN' && $role !== 'TRANSLATOR') {
            return back()->withErrors(['error' => 'Admin hanya dapat mendaftarkan akun Penerjemah.']);
        }

        $skNumber = trim($request->sk_number);
        if ($role === 'SUPERADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD';
        }
        if ($role === 'ADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD-STAFF';
        }

        if (empty($skNumber)) {
            return back()->withErrors(['error' => 'Nomor Anggota wajib diisi untuk Penerjemah.']);
        }

        $noSkKemenkum = $request->no_sk_kemenkum ? trim($request->no_sk_kemenkum) : null;
        $tglSk = $request->tgl_sk ? trim($request->tgl_sk) : null;
        $skLengkap = $request->filled('sk_lengkap')
            ? trim($request->sk_lengkap)
            : ($noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : null);

        $durationYears = max(1, (int)\App\Models\Setting::get('member_duration_years', 1));
        $registeredAt = $request->filled('registered_at') ? \Carbon\Carbon::parse($request->registered_at) : now();
        $activatedAt = $request->filled('activated_at') ? \Carbon\Carbon::parse($request->activated_at) : now();
        $expiredAt = $request->filled('expired_at')
            ? \Carbon\Carbon::parse($request->expired_at)
            : $activatedAt->copy()->addYears($durationYears);

        $newUser = User::create([
            'role' => $role,
            'user_level' => 'reguler',
            'is_verified' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $currentUser->id,
            'registered_at' => $registeredAt,
            'activated_at' => $activatedAt,
            'expired_at' => $expiredAt,
            'name' => trim($request->name),
            'email' => trim($request->email),
            'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : null,
            'sk_number' => $skNumber,
            'no_sk_kemenkum' => $noSkKemenkum,
            'tgl_sk' => $tglSk,
            'language_services' => $request->language_services ? trim($request->language_services) : null,
            'masa_aktif' => 'Aktif s/d ' . $expiredAt->translatedFormat('d F Y'),
            'sk_lengkap' => $skLengkap,
            'bio' => $request->filled('bio') ? trim($request->bio) : $skLengkap,
            'password' => Hash::make($request->password),
        ]);

        if ($role === 'TRANSLATOR') {
            $trialBonus = (int)\App\Models\Setting::get('trial_bonus_points', 10000);
            if ($trialBonus > 0) {
                $newUser->creditPoints(
                    $trialBonus,
                    'Bonus Trial Poin Awal Registrasi Akun',
                    'trial_bonus',
                    'REG-' . $newUser->id,
                    'trial_bonus_' . $newUser->id,
                    ['created_by_admin' => $currentUser->id]
                );
            }
        }

        return redirect('/admin/users')->with('success', 'Akun pengguna baru berhasil dibuat!');
    }

    public function topupUserPoints(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);

        $request->validate([
            'points' => 'required|integer|min:1',
        ], [
            'points.required' => 'Jumlah poin top-up wajib diisi.',
            'points.integer' => 'Jumlah poin harus berupa angka bulat positif.',
            'points.min' => 'Jumlah poin minimal 1.',
        ]);

        $addPoints = (int)$request->points;
        $beforePoints = $targetUser->points;
        
        $idempotencyKey = 'admin_topup_' . date('YmdHis') . '_' . $targetUser->id . '_' . uniqid();
        $targetUser->creditPoints(
            $addPoints,
            'Topup Poin Manual oleh Admin/Superadmin (' . $currentUser->name . ')',
            'adjustment',
            'ADMIN-' . $currentUser->id,
            $idempotencyKey,
            ['admin_id' => $currentUser->id, 'admin_name' => $currentUser->name]
        );

        \App\Models\AuditLog::log(
            'TOPUP_POINTS',
            User::class,
            $targetUser->id,
            ['points' => $beforePoints],
            ['points' => $targetUser->points, 'added' => $addPoints, 'by' => $currentUser->id]
        );

        return redirect('/admin/users')->with(
            'success',
            'Berhasil menambahkan top-up ' . number_format($addPoints, 0, ',', '.') . ' Poin untuk ' . $targetUser->name . '. Total poin saat ini: ' . number_format($targetUser->points, 0, ',', '.') . ' Poin.'
        );
    }

    public function updateUser(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->role !== 'TRANSLATOR' && $currentUser->role !== 'SUPERADMIN') {
            return back()->withErrors(['error' => 'Hanya Super Admin yang dapat mengubah profil Admin/Super Admin.']);
        }

        $request->validate([
            'role' => 'required|in:TRANSLATOR,ADMIN,SUPERADMIN',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'whatsapp' => 'nullable|string|max:30',
            'sk_number' => 'nullable|string|max:255',
            'no_sk_kemenkum' => 'nullable|string|max:255',
            'tgl_sk' => 'nullable|string|max:255',
            'language_services' => 'nullable|string|max:1000',
            'masa_aktif' => 'nullable|string|max:255',
            'sk_lengkap' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:6',
        ]);

        $role = $request->role;
        if ($currentUser->role === 'ADMIN' && $role !== 'TRANSLATOR') {
            return back()->withErrors(['error' => 'Admin hanya dapat mengubah peran ke Penerjemah.']);
        }

        $skNumber = trim($request->sk_number);
        if ($role === 'SUPERADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD';
        }
        if ($role === 'ADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD-STAFF';
        }

        if (empty($skNumber)) {
            return back()->withErrors(['error' => 'Nomor Anggota wajib diisi untuk Penerjemah.']);
        }

        $noSkKemenkum = $request->no_sk_kemenkum ? trim($request->no_sk_kemenkum) : null;
        $tglSk = $request->tgl_sk ? trim($request->tgl_sk) : null;

        // Auto-sync sk_lengkap if no_sk_kemenkum or tgl_sk changed and sk_lengkap was not manually customized to a new value
        $submittedSkLengkap = $request->filled('sk_lengkap') ? trim($request->sk_lengkap) : null;
        $skChanged = ($noSkKemenkum !== $targetUser->no_sk_kemenkum) || ($tglSk !== $targetUser->tgl_sk);
        if ($skChanged && ($submittedSkLengkap === null || $submittedSkLengkap === $targetUser->sk_lengkap)) {
            $skLengkap = $noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : $submittedSkLengkap;
        } else {
            $skLengkap = $submittedSkLengkap ?: ($noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : null);
        }

        $updateData = [
            'role' => $role,
            'name' => trim($request->name),
            'email' => trim($request->email),
            'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : $targetUser->whatsapp,
            'sk_number' => $skNumber,
            'no_sk_kemenkum' => $noSkKemenkum,
            'tgl_sk' => $tglSk,
            'language_services' => $request->has('language_services') ? ($request->language_services ? trim($request->language_services) : null) : $targetUser->language_services,
            'masa_aktif' => $request->has('masa_aktif') ? ($request->masa_aktif ? trim($request->masa_aktif) : 'Seumur Hidup') : $targetUser->masa_aktif,
            'sk_lengkap' => $skLengkap,
        ];

        $durationYears = max(1, (int)\App\Models\Setting::get('member_duration_years', 1));

        if ($request->filled('registered_at')) {
            try {
                $updateData['registered_at'] = \Carbon\Carbon::parse($request->registered_at);
            } catch (\Throwable $e) {}
        }

        if ($request->filled('activated_at')) {
            try {
                $actDate = \Carbon\Carbon::parse($request->activated_at);
                $updateData['activated_at'] = $actDate;
                // If expired_at was not manually filled, auto-calculate 1 year from activated_at
                if (!$request->filled('expired_at')) {
                    $updateData['expired_at'] = $actDate->copy()->addYears($durationYears);
                }
            } catch (\Throwable $e) {}
        }

        if ($request->filled('expired_at')) {
            try {
                $expDate = \Carbon\Carbon::parse($request->expired_at);
                $updateData['expired_at'] = $expDate;
                $updateData['masa_aktif'] = 'Aktif s/d ' . $expDate->translatedFormat('d F Y');
            } catch (\Throwable $e) {}
        }

        if ($request->has('verification_status')) {
            $vStatus = strtolower(trim($request->verification_status));
            $updateData['verification_status'] = $vStatus;
            $updateData['is_verified'] = ($vStatus === 'approved');
            if ($vStatus === 'approved') {
                if (!$targetUser->verified_at) {
                    $updateData['verified_at'] = now();
                    $updateData['verified_by'] = $currentUser->id;
                }
                if (empty($updateData['activated_at']) && !$targetUser->activated_at) {
                    $actNow = now();
                    $updateData['activated_at'] = $actNow;
                    if (empty($updateData['expired_at']) && !$targetUser->expired_at) {
                        $updateData['expired_at'] = $actNow->copy()->addYears($durationYears);
                    }
                }
                $updateData['rejection_reason'] = null;
            }
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $before = $targetUser->toArray();
        $targetUser->update($updateData);
        $after = $targetUser->fresh()->toArray();

        \App\Models\AuditLog::log('ADMIN_UPDATE_USER', User::class, $targetUser->id, $before, $after);

        return redirect('/admin/users')->with('success', 'Data profil pengguna berhasil diperbarui dan disinkronkan ke seluruh hasil verifikasi!');
    }

    public function verifyUser(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);
        $before = $targetUser->toArray();

        $skNumber = $request->filled('sk_number') ? trim($request->sk_number) : $targetUser->sk_number;
        $noSkKemenkum = $request->filled('no_sk_kemenkum') ? trim($request->no_sk_kemenkum) : $targetUser->no_sk_kemenkum;
        $tglSk = $request->filled('tgl_sk') ? trim($request->tgl_sk) : $targetUser->tgl_sk;
        $languageServices = $request->filled('language_services') ? trim($request->language_services) : $targetUser->language_services;
        $skLengkap = $noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : $targetUser->sk_lengkap;

        $durationYears = max(1, (int)\App\Models\Setting::get('member_duration_years', 1));
        $activatedAt = $request->filled('activated_at') ? \Carbon\Carbon::parse($request->activated_at) : now();
        $expiredAt = $request->filled('expired_at')
            ? \Carbon\Carbon::parse($request->expired_at)
            : $activatedAt->copy()->addYears($durationYears);

        $targetUser->update([
            'sk_number' => $skNumber,
            'no_sk_kemenkum' => $noSkKemenkum,
            'tgl_sk' => $tglSk,
            'language_services' => $languageServices,
            'sk_lengkap' => $skLengkap,
            'is_verified' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $currentUser->id,
            'registered_at' => $targetUser->registered_at ?: ($targetUser->created_at ?: now()),
            'activated_at' => $activatedAt,
            'expired_at' => $expiredAt,
            'masa_aktif' => 'Aktif s/d ' . $expiredAt->translatedFormat('d F Y'),
            'rejection_reason' => null,
        ]);

        $after = $targetUser->fresh()->toArray();
        \App\Models\AuditLog::log('VERIFY_IPPTI_MEMBER', User::class, $targetUser->id, $before, $after);

        return redirect('/admin/users')->with(
            'success',
            "Pendaftar anggota baru IPPTI '{$targetUser->name}' (No. Anggota: {$targetUser->sk_number}) berhasil diverifikasi dan diaktifkan! Masa aktif berlaku s/d " . $expiredAt->translatedFormat('d F Y') . "."
        );
    }

    public function renewUserMembership(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);
        $before = $targetUser->toArray();

        $years = max(1, (int)$request->input('years', \App\Models\Setting::get('member_duration_years', 1)));

        if ($request->filled('expired_at')) {
            $newExpiredAt = \Carbon\Carbon::parse($request->expired_at);
            $newActivatedAt = $targetUser->activated_at ?: now();
        } else {
            $currentExp = $targetUser->effective_expired_at;
            if ($currentExp && $currentExp->isFuture()) {
                $newExpiredAt = $currentExp->copy()->addYears($years);
                $newActivatedAt = $targetUser->effective_activated_at ?: now();
            } else {
                $newActivatedAt = now();
                $newExpiredAt = $newActivatedAt->copy()->addYears($years);
            }
        }

        $targetUser->update([
            'is_verified' => true,
            'verification_status' => 'approved',
            'activated_at' => $newActivatedAt,
            'expired_at' => $newExpiredAt,
            'masa_aktif' => 'Aktif s/d ' . $newExpiredAt->translatedFormat('d F Y'),
        ]);

        $after = $targetUser->fresh()->toArray();
        \App\Models\AuditLog::log('RENEW_IPPTI_MEMBERSHIP', User::class, $targetUser->id, $before, $after);

        return redirect('/admin/users')->with(
            'success',
            "Keanggotaan tahunan '{$targetUser->name}' berhasil diperpanjang (+{$years} Tahun)! Masa aktif baru berlaku s/d " . $newExpiredAt->translatedFormat('d F Y') . "."
        );
    }

    public function sendExpiryReminder(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);
        $res = $targetUser->sendExpiryReminder(true);

        \App\Models\AuditLog::log('SEND_EXPIRY_REMINDER', User::class, $targetUser->id, null, [
            'email' => $targetUser->email,
            'whatsapp' => $targetUser->whatsapp,
            'email_sent' => $res['email_sent'] ?? false,
            'whatsapp_sent' => $res['whatsapp_sent'] ?? false,
            'stage' => $res['stage'] ?? null,
            'expired_at' => $targetUser->effective_expired_at ? $targetUser->effective_expired_at->format('Y-m-d') : null,
        ]);

        if (!empty($res['sent'])) {
            $channels = [];
            if (!empty($res['email_sent'])) {
                $channels[] = "Email ({$targetUser->email})";
            }
            if (!empty($res['whatsapp_sent'])) {
                $waInfo = !empty($res['whatsapp_result']['simulated']) ? "WhatsApp Kirimdev.com [Simulasi/Log]" : "WhatsApp Kirimdev.com ({$targetUser->whatsapp})";
                $channels[] = $waInfo;
            }
            $channelText = implode(' & ', $channels);
            return redirect('/admin/users')->with(
                'success',
                "Pengingat perpanjangan keanggotaan untuk '{$targetUser->name}' berhasil dikirim melalui {$channelText}!"
            );
        }

        return redirect('/admin/users')->withErrors([
            'error' => "Gagal mengirim pengingat ke {$targetUser->name}. Pastikan member memiliki alamat Email atau Nomor WhatsApp aktif."
        ]);
    }

    public function rejectUser(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $targetUser = User::findOrFail($id);
        $before = $targetUser->toArray();

        $reason = $request->filled('rejection_reason')
            ? trim($request->rejection_reason)
            : 'Data pendaftaran atau dokumen SK belum lengkap/sesuai. Silakan perbarui data profil Anda.';

        $targetUser->update([
            'is_verified' => false,
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $after = $targetUser->fresh()->toArray();
        \App\Models\AuditLog::log('REJECT_IPPTI_MEMBER', User::class, $targetUser->id, $before, $after);

        return redirect('/admin/users')->with(
            'success',
            "Verifikasi pendaftar '{$targetUser->name}' ditolak / diminta revisi. Catatan telah dikirimkan ke dashboard pendaftar."
        );
    }

    public function deleteUser($id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        if ($currentUser->id === $id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $targetUser = User::findOrFail($id);
        if ($targetUser->role !== 'TRANSLATOR' && $currentUser->role !== 'SUPERADMIN') {
            return back()->withErrors(['error' => 'Hanya Super Admin yang dapat menghapus akun Admin/Super Admin.']);
        }

        // Check if user has uploaded documents
        if (Document::where('translator_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Akun tidak bisa dihapus karena telah memiliki dokumen resmi terdaftar di sistem.']);
        }

        $targetUser->delete();

        return redirect('/admin/users')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function importTranslatorsJson(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return response()->json(['success' => false, 'error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'translators' => 'required|array'
        ]);

        $translatorsData = $request->translators;
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $defaultPasswordHash = Hash::make('penerjemah123');

        foreach ($translatorsData as $t) {
            $noAnggota = trim($t['no_anggota'] ?? '');
            $nama = trim($t['name'] ?? '');
            $email = trim($t['email'] ?? '');
            if (empty($email) && !empty($noAnggota)) {
                $email = $noAnggota . '@ippti.or.id';
            }

            $noSkKemenkum = trim($t['no_sk_kemenkum'] ?? '');
            $tglSk = trim($t['tgl_sk'] ?? '');
            $arahBahasa = trim($t['arah_bahasa'] ?? '');
            $masaAktif = trim($t['masa_aktif'] ?? '');
            $urlFoto = trim($t['url_foto'] ?? '');
            $skLengkap = trim($t['sk_lengkap'] ?? '');

            if (empty($noAnggota) || empty($nama)) {
                $skippedCount++;
                $errors[] = "Baris dilewati: Data No Anggota atau Nama kosong.";
                continue;
            }

            // Check existing by sk_number or email -> Update & Sync instead of skipping!
            $existing = User::where('sk_number', $noAnggota)
                ->orWhere('email', $email)
                ->first();

            if ($existing) {
                try {
                    $computedSkLengkap = $skLengkap ?: ($noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : $existing->sk_lengkap);
                    $existing->update([
                        'name' => $nama ?: $existing->name,
                        'sk_number' => $noAnggota ?: $existing->sk_number,
                        'language_services' => $arahBahasa ?: $existing->language_services,
                        'no_sk_kemenkum' => $noSkKemenkum ?: $existing->no_sk_kemenkum,
                        'tgl_sk' => $tglSk ?: $existing->tgl_sk,
                        'masa_aktif' => $masaAktif ?: ($existing->masa_aktif ?: 'Seumur Hidup'),
                        'sk_lengkap' => $computedSkLengkap,
                        'bio' => $computedSkLengkap ?: $existing->bio,
                        'profile_picture' => $urlFoto ?: $existing->profile_picture,
                    ]);
                    $importedCount++;
                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = "Gagal memperbarui '{$nama}': " . $e->getMessage();
                }
                continue;
            }

            try {
                User::create([
                    'email' => $email,
                    'name' => $nama,
                    'sk_number' => $noAnggota,
                    'password' => $defaultPasswordHash,
                    'role' => 'TRANSLATOR',
                    'language_services' => $arahBahasa ?: null,
                    'bio' => $skLengkap ?: "Pernyataan verifikasi Kemenkumham: SK nomor " . ($noSkKemenkum ?: 'AHU-' . $noAnggota),
                    'no_sk_kemenkum' => $noSkKemenkum ?: null,
                    'tgl_sk' => $tglSk ?: null,
                    'masa_aktif' => $masaAktif ?: null,
                    'sk_lengkap' => $skLengkap ?: null,
                    'profile_picture' => $urlFoto ?: null,
                ]);
                $importedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = "Gagal mengimpor '{$nama}': " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'importedCount' => $importedCount,
            'skippedCount' => $skippedCount,
            'errors' => $errors
        ]);
    }

    public function auditLogs()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN') {
            abort(403, 'Akses ditolak.');
        }

        $logs = \App\Models\AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(50);
        return view('admin.audit-logs', compact('logs'));
    }

    // Document Types CRUD
    public function documentTypes()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            abort(403, 'Akses ditolak.');
        }
        $types = \App\Models\DocumentType::orderBy('name', 'asc')->get();
        return view('admin.document-types', compact('types'));
    }

    public function storeDocumentType(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        $request->validate(['name' => 'required|string|max:1000']);
        
        $input = $request->name;
        $names = explode(',', $input);
        
        $addedCount = 0;
        $skippedCount = 0;
        
        foreach ($names as $name) {
            $trimmed = trim($name);
            if (empty($trimmed)) continue;
            
            if (\App\Models\DocumentType::where('name', $trimmed)->exists()) {
                $skippedCount++;
                continue;
            }
            
            $type = \App\Models\DocumentType::create(['name' => $trimmed]);
            \App\Models\AuditLog::log('CREATE_DOCUMENT_TYPE', \App\Models\DocumentType::class, $type->id, null, $type->toArray());
            $addedCount++;
        }
        
        if ($addedCount > 0) {
            $msg = $addedCount . ' tipe dokumen baru berhasil ditambahkan!';
            if ($skippedCount > 0) {
                $msg .= ' (' . $skippedCount . ' tipe dilewati karena sudah terdaftar)';
            }
            return redirect('/admin/document-types')->with('success', $msg);
        } else {
            return back()->withErrors(['error' => 'Gagal menambahkan tipe dokumen (mungkin tipe sudah terdaftar).']);
        }
    }

    public function updateDocumentType(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        $request->validate(['name' => 'required|string|max:255|unique:document_types,name,' . $id]);
        
        $type = \App\Models\DocumentType::findOrFail($id);
        $oldName = $type->name;
        $newName = trim($request->name);
        
        $before = $type->toArray();
        $type->update(['name' => $newName]);
        $after = $type->toArray();
        
        \App\Models\AuditLog::log('UPDATE_DOCUMENT_TYPE', \App\Models\DocumentType::class, $type->id, $before, $after);

        // Auto-update all matching documents (REV-10)
        $affectedDocs = Document::where('document_type', $oldName)->get();
        if ($affectedDocs->isNotEmpty()) {
            Document::where('document_type', $oldName)->update(['document_type' => $newName]);
            foreach ($affectedDocs as $doc) {
                \App\Models\AuditLog::log('AUTO_UPDATE_DOCUMENT_TYPE', Document::class, $doc->id, ['document_type' => $oldName], ['document_type' => $newName]);
            }
        }

        return redirect('/admin/document-types')->with('success', 'Tipe dokumen berhasil diperbarui!');
    }

    public function deleteDocumentType($id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        
        $type = \App\Models\DocumentType::findOrFail($id);
        
        // Prevent deletion if in use
        $count = Document::where('document_type', $type->name)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => 'Tipe dokumen tidak dapat dihapus karena sedang digunakan oleh ' . $count . ' dokumen.']);
        }
        
        $before = $type->toArray();
        $type->delete();
        
        \App\Models\AuditLog::log('DELETE_DOCUMENT_TYPE', \App\Models\DocumentType::class, $id, $before, null);
        
        return redirect('/admin/document-types')->with('success', 'Tipe dokumen berhasil dihapus.');
    }

    // Language Directions CRUD
    public function languageDirections()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            abort(403, 'Akses ditolak.');
        }
        $directions = \App\Models\LanguageDirection::orderBy('name', 'asc')->get();
        return view('admin.language-directions', compact('directions'));
    }

    public function storeLanguageDirection(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        $request->validate(['name' => 'required|string|max:1000']);
        
        $input = $request->name;
        $names = explode(',', $input);
        
        $addedCount = 0;
        $skippedCount = 0;
        
        foreach ($names as $name) {
            $trimmed = trim($name);
            if (empty($trimmed)) continue;
            
            if (\App\Models\LanguageDirection::where('name', $trimmed)->exists()) {
                $skippedCount++;
                continue;
            }
            
            $direction = \App\Models\LanguageDirection::create(['name' => $trimmed]);
            \App\Models\AuditLog::log('CREATE_LANGUAGE_DIRECTION', \App\Models\LanguageDirection::class, $direction->id, null, $direction->toArray());
            $addedCount++;
        }
        
        if ($addedCount > 0) {
            $msg = $addedCount . ' arah bahasa baru berhasil ditambahkan!';
            if ($skippedCount > 0) {
                $msg .= ' (' . $skippedCount . ' arah dilewati karena sudah terdaftar)';
            }
            return redirect('/admin/language-directions')->with('success', $msg);
        } else {
            return back()->withErrors(['error' => 'Gagal menambahkan arah bahasa (mungkin arah bahasa sudah terdaftar).']);
        }
    }

    public function updateLanguageDirection(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        $request->validate(['name' => 'required|string|max:255|unique:language_directions,name,' . $id]);
        
        $direction = \App\Models\LanguageDirection::findOrFail($id);
        $oldName = $direction->name;
        $newName = trim($request->name);
        
        $before = $direction->toArray();
        $direction->update(['name' => $newName]);
        $after = $direction->toArray();
        
        \App\Models\AuditLog::log('UPDATE_LANGUAGE_DIRECTION', \App\Models\LanguageDirection::class, $direction->id, $before, $after);

        // Auto-update all matching documents (REV-10)
        $affectedDocs = Document::where('language_pair', $oldName)->get();
        if ($affectedDocs->isNotEmpty()) {
            Document::where('language_pair', $oldName)->update(['language_pair' => $newName]);
            foreach ($affectedDocs as $doc) {
                \App\Models\AuditLog::log('AUTO_UPDATE_LANGUAGE_PAIR', Document::class, $doc->id, ['language_pair' => $oldName], ['language_pair' => $newName]);
            }
        }

        return redirect('/admin/language-directions')->with('success', 'Arah bahasa berhasil diperbarui!');
    }

    public function deleteLanguageDirection($id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }
        
        $direction = \App\Models\LanguageDirection::findOrFail($id);
        
        // Prevent deletion if in use
        $count = Document::where('language_pair', $direction->name)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => 'Arah bahasa tidak dapat dihapus karena sedang digunakan oleh ' . $count . ' dokumen.']);
        }
        
        $before = $direction->toArray();
        $direction->delete();
        
        \App\Models\AuditLog::log('DELETE_LANGUAGE_DIRECTION', \App\Models\LanguageDirection::class, $id, $before, null);
        
        return redirect('/admin/language-directions')->with('success', 'Arah bahasa berhasil dihapus.');
    }

    public function updateIpaymuSettings(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $request->validate([
            'ipaymu_va' => 'nullable|string|max:255',
            'ipaymu_api_key' => 'nullable|string|max:255',
            'ipaymu_webhook_secret' => 'nullable|string|max:255',
            'ipaymu_env' => 'required|in:sandbox,production',
        ]);

        $va = trim((string)$request->ipaymu_va);
        $apiKey = trim((string)$request->ipaymu_api_key);
        $webhookSecret = trim((string)$request->ipaymu_webhook_secret);
        $env = strtolower(trim((string)$request->ipaymu_env));

        // 1. Save directly into dynamic Setting table (persistent database storage)
        \App\Models\Setting::set('xenith_access_key', $va);
        \App\Models\Setting::set('xenith_secret_key', $apiKey);
        if (!empty($webhookSecret)) {
            \App\Models\Setting::set('xenith_webhook_secret', $webhookSecret);
        }
        \App\Models\Setting::set('xenith_env', $env);

        \App\Models\Setting::set('ipaymu_va', $va);
        \App\Models\Setting::set('ipaymu_api_key', $apiKey);
        \App\Models\Setting::set('ipaymu_env', $env);

        // 2. Also attempt to update .env if writable
        try {
            $envPath = base_path('.env');
            if (file_exists($envPath) && is_writable($envPath)) {
                $envContent = file_get_contents($envPath);
                
                $keys = [
                    'XENITH_ACCESS_KEY' => $va,
                    'XENITH_SECRET_KEY' => $apiKey,
                    'XENITH_WEBHOOK_SECRET' => $webhookSecret,
                    'XENITH_ENV' => $env,
                    'IPAYMU_VA' => $va,
                    'IPAYMU_API_KEY' => $apiKey,
                    'IPAYMU_ENV' => $env,
                ];

                foreach ($keys as $key => $value) {
                    $safeVal = preg_replace('/[\r\n"\\\\$]/', '', (string)$value);
                    if (empty($safeVal) && $key === 'XENITH_WEBHOOK_SECRET') continue;
                    if (preg_match("/^{$key}=.*/m", $envContent)) {
                        $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$safeVal}\"", $envContent);
                    } else {
                        $envContent .= "\n{$key}=\"{$safeVal}\"";
                    }
                }

                file_put_contents($envPath, $envContent);
            }
        } catch (\Throwable $e) {}

        \App\Models\AuditLog::log('UPDATE_PAYMENT_GATEWAY_SETTINGS', User::class, $currentUser->id, [], [
            'env' => $env,
            'va_length' => strlen($va),
            'has_webhook_secret' => !empty($webhookSecret),
        ]);

        $envLabel = $env === 'production' ? 'Production (Live)' : 'Sandbox (Testing)';
        return back()->with('success', "Pengaturan Payment Gateway Xenith Pay ({$envLabel}) berhasil disimpan dan aktif!");
    }

    public function settings()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            abort(403, 'Akses ditolak.');
        }

        $settings = \App\Models\Setting::getAll();

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $request->validate([
            'trial_bonus_points' => 'required|numeric|min:0',
            'min_topup_amount' => 'required|numeric|min:10000',
            'low_point_threshold' => 'required|numeric|min:0',
            'member_registration_fee' => 'nullable|numeric|min:0',
            'member_renewal_fee' => 'nullable|numeric|min:0',
            'member_duration_years' => 'nullable|integer|min:1|max:10',
            'member_reminder_days_1' => 'nullable|integer|min:1|max:365',
            'member_reminder_days_2' => 'nullable|integer|min:1|max:365',
        ]);

        $keys = [
            'trial_bonus_points',
            'min_topup_amount',
            'pro_activation_price',
            'pro_activation_points',
            'low_point_threshold',
            'member_registration_fee',
            'member_renewal_fee',
            'member_duration_years',
            'member_reminder_days_1',
            'member_reminder_days_2',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                \App\Models\Setting::set($key, $request->input($key));
            }
        }

        \App\Models\Setting::set('member_reminder_email_enabled', $request->boolean('member_reminder_email_enabled') ? '1' : '0');
        \App\Models\Setting::set('member_reminder_whatsapp_enabled', $request->boolean('member_reminder_whatsapp_enabled') ? '1' : '0');

        \App\Models\AuditLog::log('UPDATE_SYSTEM_SETTINGS', User::class, $currentUser->id, [], $request->only(array_merge($keys, [
            'member_reminder_email_enabled',
            'member_reminder_whatsapp_enabled',
        ])));

        return redirect('/admin/settings')->with('success', 'Pengaturan biaya, jadwal reminder H-30 & H-10, serta konfigurasi aplikasi berhasil diperbarui!');
    }

    public function updateKirimdevSettings(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $request->validate([
            'kirimdev_api_key' => 'nullable|string|max:500',
            'kirimdev_phone_number_id' => 'nullable|string|max:150',
            'kirimdev_base_url' => 'nullable|url|max:255',
            'member_reminder_days_1' => 'nullable|integer|min:1|max:365',
            'member_reminder_days_2' => 'nullable|integer|min:1|max:365',
        ]);

        $apiKey = trim((string)$request->input('kirimdev_api_key', ''));
        $phoneId = trim((string)$request->input('kirimdev_phone_number_id', ''));
        $baseUrl = rtrim(trim((string)$request->input('kirimdev_base_url', 'https://api.kirimdev.com/v1')), '/');

        if (!empty($apiKey)) {
            \App\Models\Setting::set('kirimdev_api_key', $apiKey);
        }
        \App\Models\Setting::set('kirimdev_phone_number_id', $phoneId);
        \App\Models\Setting::set('kirimdev_base_url', $baseUrl ?: 'https://api.kirimdev.com/v1');

        if ($request->filled('member_reminder_days_1')) {
            \App\Models\Setting::set('member_reminder_days_1', (int)$request->input('member_reminder_days_1'));
        }
        if ($request->filled('member_reminder_days_2')) {
            \App\Models\Setting::set('member_reminder_days_2', (int)$request->input('member_reminder_days_2'));
        }
        if ($request->has('member_reminder_email_enabled') || $request->has('reminder_channels_form')) {
            \App\Models\Setting::set('member_reminder_email_enabled', $request->boolean('member_reminder_email_enabled') ? '1' : '0');
            \App\Models\Setting::set('member_reminder_whatsapp_enabled', $request->boolean('member_reminder_whatsapp_enabled') ? '1' : '0');
        }

        try {
            $envPath = base_path('.env');
            if (file_exists($envPath) && is_writable($envPath)) {
                $envContent = file_get_contents($envPath);
                $envKeys = [
                    'KIRIMDEV_API_KEY' => $apiKey,
                    'KIRIMDEV_PHONE_NUMBER_ID' => $phoneId,
                    'KIRIMDEV_BASE_URL' => $baseUrl ?: 'https://api.kirimdev.com/v1',
                ];
                foreach ($envKeys as $k => $v) {
                    $safeVal = preg_replace('/[\r\n"\\\\$]/', '', (string)$v);
                    if ($k === 'KIRIMDEV_API_KEY' && empty($safeVal)) continue;
                    if (preg_match("/^{$k}=.*/m", $envContent)) {
                        $envContent = preg_replace("/^{$k}=.*/m", "{$k}=\"{$safeVal}\"", $envContent);
                    } else {
                        $envContent .= "\n{$k}=\"{$safeVal}\"";
                    }
                }
                file_put_contents($envPath, $envContent);
            }
        } catch (\Throwable $e) {}

        \App\Models\AuditLog::log('UPDATE_KIRIMDEV_WHATSAPP_SETTINGS', User::class, $currentUser->id, [], [
            'phone_number_id' => $phoneId,
            'base_url' => $baseUrl,
            'has_api_key' => !empty($apiKey),
        ]);

        return back()->with('success', 'Pengaturan WhatsApp Gateway (Kirimdev.com) & Jadwal Reminder Perpanjangan berhasil disimpan!');
    }

    public function testKirimdevWhatsapp(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'SUPERADMIN' && $currentUser->role !== 'ADMIN') {
            return back()->withErrors(['error' => 'Akses ditolak.']);
        }

        $request->validate([
            'test_phone' => 'required|string|max:30',
        ]);

        $d1 = (int)\App\Models\Setting::get('member_reminder_days_1', 30);
        $d2 = (int)\App\Models\Setting::get('member_reminder_days_2', 10);
        $renewalFee = number_format((float)\App\Models\Setting::get('member_renewal_fee', 700000), 0, ',', '.');
        $payUrl = url('/admin?pay_renewal=1');

        $message = "*[DocVerify IPPTI - Test Reminder WhatsApp Kirimdev.com]*\n\n"
            . "Halo *{$currentUser->name}*,\n"
            . "Integrasi WhatsApp Gateway *Kirimdev.com* pada portal DocVerify IPPTI telah aktif!\n\n"
            . "• Jadwal Reminder 1: *H-{$d1} ({$d1} hari sebelum expired)*\n"
            . "• Jadwal Reminder 2: *H-{$d2} ({$d2} hari sebelum expired)*\n"
            . "• Biaya Perpanjangan Tahunan: *Rp {$renewalFee}*\n"
            . "• Link Pembayaran: {$payUrl}\n\n"
            . "_Pesan ini dikirim otomatis dari Pengaturan Sistem DocVerify IPPTI._";

        $result = User::sendWhatsappViaKirimdev($request->test_phone, $message);

        if (!empty($result['success'])) {
            $modeNote = !empty($result['simulated'])
                ? ' (Mode Simulasi/Log karena API Key atau Phone Number ID Kirimdev.com belum diisi)'
                : '';
            return back()->with('success', "Pesan uji coba WhatsApp Kirimdev.com berhasil dikirim ke {$result['to']}{$modeNote}!");
        }

        return back()->withErrors([
            'error' => "Gagal mengirim uji coba WhatsApp ke {$request->test_phone}: " . ($result['message'] ?? 'Unknown error')
        ]);
    }

    public function upgrade()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'TRANSLATOR') {
            return redirect('/admin');
        }

        $minTopup = (float)\App\Models\Setting::get('min_topup_amount', 100000);

        return view('admin.upgrade', compact('minTopup'));
    }

    public function transactions()
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect('/login');
        }

        $query = \App\Models\PointTransaction::orderBy('created_at', 'desc');

        if ($currentUser->role === 'TRANSLATOR') {
            $query->where('user_id', $currentUser->id);
        }

        $transactions = $query->paginate(20);

        $totalCredit = \App\Models\PointTransaction::where('user_id', $currentUser->id)->where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\PointTransaction::where('user_id', $currentUser->id)->where('type', 'debit')->sum('amount');

        return view('admin.transactions', compact('transactions', 'totalCredit', 'totalDebit'));
    }
}
