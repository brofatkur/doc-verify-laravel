<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }
        return view('login');
    }

    public function doLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = 'login:' . strtolower(trim($credentials['email'])) . '|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login gagal. Silakan tunggu {$seconds} detik sebelum mencoba kembali.",
            ])->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            // Check if 2FA (Google Authenticator TOTP) is enabled
            if ($user->hasTwoFactorEnabled()) {
                $request->session()->put('2fa_pending_user_id', $user->id);
                $request->session()->put('2fa_pending_expires', now()->addMinutes(10)->timestamp);

                return redirect('/login/2fa');
            }

            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showTwoFactorForm(Request $request)
    {
        if (Auth::check()) {
            return redirect('/admin');
        }

        $userId = $request->session()->get('2fa_pending_user_id');
        $expires = (int)$request->session()->get('2fa_pending_expires', 0);

        if (!$userId || $expires < now()->timestamp) {
            $request->session()->forget(['2fa_pending_user_id', '2fa_pending_expires']);
            return redirect('/login')->withErrors(['email' => 'Sesi verifikasi 2FA telah berakhir. Silakan login kembali.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect('/login');
        }

        $method = strtolower((string)($user->two_factor_method ?? 'otp'));
        $emailParts = explode('@', $user->email);
        $maskedEmail = substr($emailParts[0], 0, 2) . '***@' . ($emailParts[1] ?? '');
        $maskedWhatsapp = $user->whatsapp ? (substr($user->whatsapp, 0, 4) . '****' . substr($user->whatsapp, -3)) : null;

        return view('auth.verify-2fa', compact('method', 'maskedEmail', 'maskedWhatsapp'));
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:10',
        ], [
            'code.required' => 'Kode verifikasi 6-digit wajib diisi.',
        ]);

        $userId = $request->session()->get('2fa_pending_user_id');
        $expires = (int)$request->session()->get('2fa_pending_expires', 0);

        if (!$userId || $expires < now()->timestamp) {
            $request->session()->forget(['2fa_pending_user_id', '2fa_pending_expires']);
            return redirect('/login')->withErrors(['email' => 'Sesi verifikasi 2FA telah berakhir. Silakan login kembali.']);
        }

        $throttleKey = '2fa_verify:' . $userId . '|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['code' => "Terlalu banyak percobaan kode 2FA salah. Silakan tunggu {$seconds} detik."]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect('/login');
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $request->session()->forget(['2fa_pending_user_id', '2fa_pending_expires']);

            Auth::login($user);
            $request->session()->regenerate();

            \App\Models\AuditLog::log('LOGIN_2FA_VERIFIED', User::class, $user->id, null, [
                'method' => $user->two_factor_method,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended('/admin');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'code' => 'Kode verifikasi 2FA tidak valid atau sudah kadaluarsa.',
        ]);
    }

    public function resendTwoFactorOtp(Request $request)
    {
        $userId = $request->session()->get('2fa_pending_user_id');
        if (!$userId) {
            return redirect('/login');
        }

        $throttleKey = '2fa_resend:' . $userId;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['code' => "Mohon tunggu {$seconds} detik sebelum meminta pengiriman ulang OTP."]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect('/login');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
        $user->sendTwoFactorOtp();

        return back()->with('success', 'Kode OTP 6-digit baru telah dikirimkan ke Email & WhatsApp Anda!');
    }

    public function updateTwoFactorSettings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'two_factor_method' => 'required|in:none,totp',
            'totp_secret' => 'nullable|string|max:64',
            'totp_verify_code' => 'nullable|string|max:10',
        ]);

        $method = strtolower($request->two_factor_method);

        if ($method === 'totp') {
            $secret = strtoupper(trim((string)($request->totp_secret ?: $user->two_factor_secret)));
            $code = trim((string)$request->totp_verify_code);

            if (empty($secret)) {
                return back()->withErrors(['totp_verify_code' => 'Secret key Google Authenticator tidak ditemukan.']);
            }

            // Require verifying a 6-digit code from Google Authenticator app when activating TOTP
            if (!empty($code) || $user->two_factor_method !== 'totp') {
                if (empty($code) || !\App\Support\TotpAuthenticator::verifyCode($secret, $code)) {
                    return back()->withErrors([
                        'totp_verify_code' => 'Kode 6-digit Google Authenticator tidak cocok. Pastikan Anda sudah memindai QR Code dan memasukkan kode terbaru dari aplikasi Authenticator.',
                    ]);
                }
            }

            $user->update([
                'two_factor_method' => 'totp',
                'two_factor_secret' => $secret,
            ]);

            \App\Models\AuditLog::log('ENABLE_2FA_TOTP', User::class, $user->id, null, ['method' => 'totp']);
            return back()->with('success', 'Keamanan 2FA Google Authenticator berhasil diaktifkan pada akun Anda!');
        }

        if ($method === 'otp') {
            $user->update([
                'two_factor_method' => 'otp',
            ]);

            \App\Models\AuditLog::log('ENABLE_2FA_OTP', User::class, $user->id, null, ['method' => 'otp']);
            return back()->with('success', 'Keamanan 2FA Kode OTP (via Email & WhatsApp Kirimdev.com) berhasil diaktifkan pada akun Anda!');
        }

        $user->update([
            'two_factor_method' => 'none',
            'two_factor_otp_code' => null,
            'two_factor_otp_expires_at' => null,
        ]);

        \App\Models\AuditLog::log('DISABLE_2FA', User::class, $user->id, null, ['method' => 'none']);
        return back()->with('success', 'Verifikasi 2FA telah dinonaktifkan.');
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }
        $languageDirections = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('language_directions')) {
                $languageDirections = \App\Models\LanguageDirection::orderBy('name', 'asc')->get();
            }
        } catch (\Throwable $e) {}

        $registrationFee = (float)\App\Models\Setting::get('member_registration_fee', 850000);
        $renewalFee = (float)\App\Models\Setting::get('member_renewal_fee', 700000);

        return view('register', compact('languageDirections', 'registrationFee', 'renewalFee'));
    }

    public function doRegister(Request $request)
    {
        $skNumber = trim((string)$request->sk_number);
        $email = trim((string)$request->email);

        // Find if user already exists with the skNumber (when provided)
        $existingBySk = null;
        if (!empty($skNumber)) {
            $existingBySk = User::where('sk_number', $skNumber)->where('role', 'TRANSLATOR')->first();
        }

        // If member already registered with real email, inform to login
        if ($existingBySk && !str_ends_with($existingBySk->email, '@ippti.or.id')) {
            return back()->withErrors([
                'sk_number' => 'Nomor anggota ini sudah terdaftar, silahkan login.'
            ])->with('already_registered', true)->withInput();
        }

        // Validate email uniqueness except for the pre-imported user
        $emailRules = 'required|string|email|max:255';
        if ($existingBySk) {
            $emailDup = User::where('email', $email)->where('id', '!=', $existingBySk->id)->first();
            if ($emailDup) {
                return back()->withErrors(['email' => 'Email ini sudah terdaftar.'])->withInput();
            }
        } else {
            $emailRules .= '|unique:users';
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'sk_number' => 'nullable|string|max:255',
            'no_sk_kemenkum' => 'required|string|max:255',
            'tgl_sk' => 'required|string|max:255',
            'language_services' => 'required|string|max:1000',
            'whatsapp' => 'required|string|max:30',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:150',
            'email' => $emailRules,
            'password' => 'required|string|min:6|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sk_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
        ], [
            'name.required' => 'Nama lengkap & gelar wajib diisi.',
            'no_sk_kemenkum.required' => 'Nomor SK Menteri Hukum Republik Indonesia wajib diisi.',
            'tgl_sk.required' => 'Tanggal SK Menteri Hukum Republik Indonesia wajib diisi.',
            'language_services.required' => 'Pasangan / Arah Bahasa Tersumpah wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'profile_picture.image' => 'Foto profil harus berupa berkas gambar.',
            'profile_picture.max' => 'Ukuran foto profil maksimal 2MB.',
            'sk_document.mimes' => 'Salinan SK harus berformat PDF, JPG, PNG, atau WEBP.',
            'sk_document.max' => 'Ukuran berkas SK maksimal 5MB.',
        ]);

        // Auto-generate candidate member number if applicant does not have an IPPTI Member Number yet
        if (empty($skNumber)) {
            do {
                $skNumber = 'REG-' . date('ym') . rand(100, 999);
            } while (User::where('sk_number', $skNumber)->exists());
        }

        $noSkKemenkum = trim((string)$request->no_sk_kemenkum);
        $tglSk = trim((string)$request->tgl_sk);
        $languageServices = trim((string)$request->language_services);
        $skLengkap = trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : ''));

        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->storeUploadedFile($request->file('profile_picture'), 'profile-reg-' . time() . '-' . uniqid());
        }

        $skDocumentPath = null;
        if ($request->hasFile('sk_document')) {
            $skDocumentPath = $this->storeUploadedFile($request->file('sk_document'), 'sk-doc-' . time() . '-' . uniqid());
        }

        if ($existingBySk) {
            $updatePayload = [
                'name' => trim($request->name),
                'nik' => $request->nik ? trim($request->nik) : $existingBySk->nik,
                'email' => $email,
                'password' => Hash::make($request->password),
                'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : $existingBySk->whatsapp,
                'address' => $request->address ? trim($request->address) : $existingBySk->address,
                'city' => $request->city ? trim($request->city) : $existingBySk->city,
                'no_sk_kemenkum' => $noSkKemenkum ?: $existingBySk->no_sk_kemenkum,
                'tgl_sk' => $tglSk ?: $existingBySk->tgl_sk,
                'language_services' => $languageServices ?: $existingBySk->language_services,
                'sk_lengkap' => $skLengkap ?: $existingBySk->sk_lengkap,
                'bio' => $request->filled('bio') ? trim($request->bio) : ($skLengkap ?: $existingBySk->bio),
                'is_verified' => false,
                'verification_status' => 'pending',
                'registered_at' => $existingBySk->registered_at ?: now(),
                'activated_at' => null,
                'expired_at' => null,
            ];
            if ($profilePicturePath) {
                $updatePayload['profile_picture'] = $profilePicturePath;
            }
            if ($skDocumentPath) {
                $updatePayload['sk_document_path'] = $skDocumentPath;
            }
            $existingBySk->update($updatePayload);
            $user = $existingBySk;
        } else {
            $user = User::create([
                'name' => trim($request->name),
                'nik' => $request->nik ? trim($request->nik) : null,
                'sk_number' => $skNumber,
                'email' => $email,
                'password' => Hash::make($request->password),
                'role' => 'TRANSLATOR',
                'user_level' => 'reguler',
                'is_verified' => false,
                'verification_status' => 'pending',
                'registered_at' => now(),
                'activated_at' => null,
                'expired_at' => null,
                'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : null,
                'address' => $request->address ? trim($request->address) : null,
                'city' => $request->city ? trim($request->city) : null,
                'no_sk_kemenkum' => $noSkKemenkum,
                'tgl_sk' => $tglSk,
                'language_services' => $languageServices,
                'masa_aktif' => 'Seumur Hidup',
                'sk_lengkap' => $skLengkap,
                'bio' => $request->filled('bio') ? trim($request->bio) : $skLengkap,
                'profile_picture' => $profilePicturePath,
                'sk_document_path' => $skDocumentPath,
            ]);
        }

        // Credit Trial Bonus Points upon registration
        $trialBonus = (int)\App\Models\Setting::get('trial_bonus_points', 10000);
        if ($trialBonus > 0 && $user->role === 'TRANSLATOR') {
            $user->creditPoints(
                $trialBonus,
                'Bonus Trial Poin Awal Registrasi Anggota IPPTI',
                'trial_bonus',
                'REG-' . $user->id,
                'trial_bonus_' . $user->id,
                ['welcome_bonus' => true]
            );
        }

        \App\Models\AuditLog::log('REGISTER_NEW_IPPTI_MEMBER', User::class, $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'sk_number' => $user->sk_number,
            'no_sk_kemenkum' => $user->no_sk_kemenkum,
            'verification_status' => 'pending',
        ]);

        Auth::login($user);

        // Immediately redirect new registrant to Payment Gateway checkout page (Rp 850.000)
        $paymentUrl = app(\App\Http\Controllers\PaymentController::class)->generateMembershipPaymentUrl($user, 'initial');
        if (!empty($paymentUrl)) {
            return redirect()->away($paymentUrl);
        }

        return redirect('/admin')->with(
            'success',
            'Pendaftaran Anggota Baru IPPTI berhasil dikirim! Silakan selesaikan pembayaran pendaftaran awal dan tunggu verifikasi Pengurus Admin IPPTI.'
        );
    }

    public function checkMember($memberNo)
    {
        if (empty($memberNo)) {
            return response()->json(['success' => false, 'error' => 'Nomor anggota wajib diisi.'], 400);
        }

        $user = User::where('sk_number', $memberNo)->where('role', 'TRANSLATOR')->first();

        if ($user) {
            $isAlreadyRegistered = !str_ends_with($user->email, '@ippti.or.id');
            if ($isAlreadyRegistered) {
                return response()->json([
                    'success' => false,
                    'already_registered' => true,
                    'error' => 'Anda sudah terdaftar, silahkan login.',
                    'message' => 'Anda sudah terdaftar, silahkan login.',
                    'login_url' => '/login',
                ]);
            }

            return response()->json([
                'success' => true,
                'translator' => [
                    'name' => $user->name,
                    'email' => str_ends_with($user->email, '@ippti.or.id') ? '' : $user->email,
                    'whatsapp' => $user->whatsapp ?: '',
                    'no_sk_kemenkum' => $user->effective_no_sk !== $user->sk_number ? $user->effective_no_sk : ($user->no_sk_kemenkum ?: ''),
                    'tgl_sk' => $user->effective_tgl_sk ?: '',
                    'language_services' => $user->language_services ?: '',
                ]
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Nomor anggota tidak ditemukan dalam database pra-impor.'], 404);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $rules = [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'whatsapp' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:150',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sk_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
        ];

        // Allow unverified translator applicants or admins to edit name and language_services
        if ($user->role !== 'TRANSLATOR' || !$user->isVerifiedTranslator()) {
            $rules['name'] = 'required|string|max:255';
            $rules['language_services'] = 'nullable|string';
        }

        $request->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'profile_picture.image' => 'Berkas harus berupa gambar.',
            'profile_picture.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'profile_picture.max' => 'Ukuran gambar maksimal 2MB.',
            'sk_document.mimes' => 'Salinan SK harus berformat PDF, JPG, PNG, atau WEBP.',
            'sk_document.max' => 'Ukuran berkas SK maksimal 5MB.',
        ]);

        $oldEmail = $user->email;
        $oldWhatsapp = $user->whatsapp;
        $emailChanged = ($request->email !== $oldEmail);
        $whatsappChanged = ($request->whatsapp !== $oldWhatsapp);

        // Send email notification upon contact info change (REV-11)
        if ($emailChanged || $whatsappChanged) {
            $alertContent = "\n======================================================\n";
            $alertContent .= "[ALERT EMAIL NOTIFICATION SENT TO {$oldEmail}]:\n";
            $alertContent .= "Halo {$user->name},\n";
            $alertContent .= "Informasi kontak Anda di portal DocVerify IPPTI telah diperbarui:\n";
            if ($emailChanged) {
                $alertContent .= " - Alamat Email: {$oldEmail} -> {$request->email}\n";
            }
            if ($whatsappChanged) {
                $alertContent .= " - WhatsApp / HP: {$oldWhatsapp} -> {$request->whatsapp}\n";
            }
            $alertContent .= "Jika Anda tidak merasa melakukan perubahan ini, hubungi administrator IPPTI.\n";
            $alertContent .= "======================================================\n";
            error_log($alertContent);
        }

        $noSkKemenkum = $request->no_sk_kemenkum ? trim($request->no_sk_kemenkum) : null;
        $tglSk = $request->tgl_sk ? trim($request->tgl_sk) : null;
        $skLengkap = $request->filled('sk_lengkap')
            ? trim($request->sk_lengkap)
            : ($noSkKemenkum ? trim($noSkKemenkum . ($tglSk ? ' tanggal ' . $tglSk : '')) : $user->sk_lengkap);

        $updateData = [
            'email' => trim($request->email),
            'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : null,
            'nik' => $request->has('nik') ? ($request->nik ? trim($request->nik) : null) : $user->nik,
            'address' => $request->has('address') ? ($request->address ? trim($request->address) : null) : $user->address,
            'city' => $request->has('city') ? ($request->city ? trim($request->city) : null) : $user->city,
            'no_sk_kemenkum' => $noSkKemenkum,
            'tgl_sk' => $tglSk,
            'sk_lengkap' => $skLengkap,
            'bio' => $request->bio,
        ];

        // Prevent verified translator from editing their own name or language services (REV-11), but allow unverified applicant to revise
        if ($user->role !== 'TRANSLATOR' || !$user->isVerifiedTranslator()) {
            if ($request->filled('name')) {
                $updateData['name'] = trim($request->name);
            }
            if ($request->has('language_services')) {
                $updateData['language_services'] = $request->language_services ? trim($request->language_services) : null;
            }
        }

        // If previously rejected, updating profile resubmits for verification
        if ($user->role === 'TRANSLATOR' && $user->verification_status === 'rejected') {
            $updateData['verification_status'] = 'pending';
        }

        if ($request->hasFile('profile_picture')) {
            $savedPhoto = $this->storeUploadedFile($request->file('profile_picture'), 'profile-' . $user->id . '-' . time());
            if ($savedPhoto) {
                $updateData['profile_picture'] = $savedPhoto;
            }
        }

        if ($request->hasFile('sk_document')) {
            $savedSk = $this->storeUploadedFile($request->file('sk_document'), 'sk-doc-' . $user->id . '-' . time());
            if ($savedSk) {
                $updateData['sk_document_path'] = $savedSk;
            }
        }

        $before = $user->toArray();
        $user->update($updateData);
        
        $updatedUser = User::find($user->id);
        $after = $updatedUser->toArray();

        \App\Models\AuditLog::log('UPDATE_PROFILE', User::class, $user->id, $before, $after);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    public function forgotPasswordView()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.'
        ]);

        $email = trim($request->email);
        $throttleKey = 'forgot_password:' . strtolower($email) . '|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['email' => "Terlalu banyak permintaan reset password. Silakan tunggu {$seconds} detik."]);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('status', 'Jika email terdaftar, instruksi reset password telah dikirim.');
        }

        if (str_ends_with($user->email, '@ippti.or.id')) {
            return back()->withErrors(['email' => 'Akun Anda belum diklaim/diaktifkan. Silakan lakukan pendaftaran terlebih dahulu menggunakan Nomor Anggota Anda.']);
        }

        $token = Str::random(60);
        $hashedToken = hash('sha256', $token);

        // Save SHA-256 hashed token to DB
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $hashedToken,
                'created_at' => now()
            ]
        );

        $resetLink = url('/reset-password?token=' . $token);

        // Send via Email
        try {
            $subject = "[DocVerify IPPTI] Permintaan Reset Password Akun - {$user->name}";
            $body = "Yth. {$user->name},\n\n"
                . "Kami menerima permintaan reset password untuk akun DocVerify IPPTI Anda ({$email}).\n\n"
                . "Silakan klik tautan berikut untuk membuat password baru (berlaku selama 60 menit):\n"
                . "{$resetLink}\n\n"
                . "Jika Anda tidak merasa meminta reset password, abaikan pesan ini.\n\n"
                . "Hormat kami,\n"
                . "Pengurus Nasional IPPTI";
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($email, $user, $subject) {
                $message->to($email, $user->name)->subject($subject);
            });
        } catch (\Throwable $e) {}

        // Also send via WhatsApp (Kirimdev.com) if user has whatsapp number
        if (!empty($user->whatsapp)) {
            $waBody = "*[DocVerify IPPTI - Reset Password]*\n\n"
                . "Yth. *{$user->name}*,\n"
                . "Klik tautan berikut untuk mereset password akun DocVerify IPPTI Anda (berlaku 60 menit):\n"
                . "{$resetLink}\n\n"
                . "_Abaikan pesan ini jika Anda tidak meminta reset password._";
            User::sendWhatsappViaKirimdev($user->whatsapp, $waBody);
        }

        return back()->with('status', 'Link reset password telah dikirim ke Email & WhatsApp terdaftar Anda (berlaku 60 menit).');
    }

    public function resetPasswordView(Request $request)
    {
        $token = $request->query('token');
        return view('auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'token.required' => 'Token reset wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.'
        ]);

        $hashedToken = hash('sha256', (string)$request->token);
        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('token', $hashedToken)
            ->orWhere('token', $request->token)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kadaluarsa.']);
        }

        // Enforce 60-minute token expiry
        if (!empty($tokenRecord->created_at) && \Carbon\Carbon::parse($tokenRecord->created_at)->addMinutes(60)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $tokenRecord->email)
                ->delete();
            return back()->withErrors(['email' => 'Token reset password telah kadaluarsa (lebih dari 60 menit). Silakan ajukan ulang.']);
        }

        // Update password
        User::where('email', $tokenRecord->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Clean token
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $tokenRecord->email)
            ->delete();

        return redirect('/login')->with('success', 'Password Anda berhasil diperbarui. Silakan masuk kembali.');
    }

    public function searchTranslators(Request $request)
    {
        $query = $request->query('query');
        if (empty($query)) {
            return response()->json(['success' => false, 'error' => 'Query pencarian kosong.']);
        }

        // Endpoint security: require minimal characters (REV-19)
        if (strlen(trim($query)) < 2) {
            return response()->json(['success' => false, 'error' => 'Kata kunci pencarian terlalu pendek.']);
        }

        // Exclude Admin/Superadmin and unverified accounts from public search (REV-13)
        $queryBuilder = User::where('role', 'TRANSLATOR')
            ->where(function ($q) {
                $q->where('is_verified', true)->orWhereNull('is_verified');
            });

        // Token-AND logic: keywords can match in any order (REV-07)
        $words = array_filter(explode(' ', trim($query)));
        foreach ($words as $word) {
            $cleanWord = strtolower($word);
            $queryBuilder->where(function ($q) use ($cleanWord) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$cleanWord}%"])
                  ->orWhereRaw('LOWER(sk_number) LIKE ?', ["%{$cleanWord}%"])
                  ->orWhereRaw('LOWER(bio) LIKE ?', ["%{$cleanWord}%"])
                  ->orWhereRaw('LOWER(language_services) LIKE ?', ["%{$cleanWord}%"]);
            });
        }

        $translators = $queryBuilder
            ->select('id', 'name', 'sk_number', 'language_services', 'profile_picture')
            ->get();

        return response()->json(['success' => true, 'translators' => $translators]);
    }

    public function showPublicTranslator($translatorId)
    {
        $translator = User::where('id', $translatorId)
            ->orWhere('sk_number', $translatorId)
            ->orderBy('updated_at', 'desc')
            ->firstOrFail();

        if ($translator->role !== 'TRANSLATOR' || !$translator->isVerifiedTranslator()) {
            abort(404);
        }

        return view('verify-translator', compact('translator'));
    }

    public function showInstallForm()
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                $hasSuperAdmin = User::where('role', 'SUPERADMIN')->exists();
                if ($hasSuperAdmin) {
                    return redirect('/login')->withErrors(['email' => 'Super Admin sudah terinstal. Akses konfigurasi /install dinonaktifkan.']);
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet, which is fine, we will migrate it on submit
        }

        return view('auth.install');
    }

    public function installSuperAdmin(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal menjalankan setup awal database: ' . $e->getMessage()])->withInput();
        }

        try {
            $hasSuperAdmin = User::where('role', 'SUPERADMIN')->exists();
            if ($hasSuperAdmin) {
                return redirect('/login')->withErrors(['email' => 'Super Admin sudah terinstal. Akses konfigurasi /install dinonaktifkan.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Database error: ' . $e->getMessage()])->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email resmi wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'SUPERADMIN',
            'sk_number' => 'IPPTI-HQ-2026',
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/admin')->with('success', 'Instalasi berhasil! Anda telah masuk sebagai Super Admin.');
    }

    /**
     * Store uploaded file safely with multi-directory fallback (public/uploads -> storage/app/public/uploads)
     * so permission mismatches on public/uploads never cause FileException.
     */
    protected function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $basePrefix): ?string
    {
        // Strict MIME-based extension whitelist (prevents PHP/script execution attacks)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        $detectedExt = strtolower((string)$file->extension());
        if (!in_array($detectedExt, $allowedExtensions, true)) {
            $clientExt = strtolower((string)$file->getClientOriginalExtension());
            $detectedExt = in_array($clientExt, $allowedExtensions, true) ? $clientExt : 'jpg';
        }
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '', $basePrefix) . '.' . $detectedExt;

        $publicDir = public_path('uploads');
        $storageDir = storage_path('app/public/uploads');

        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0775, true);
        }

        // Write protective .htaccess in public/uploads to block PHP execution
        $htaccessPath = $publicDir . '/.htaccess';
        if (is_dir($publicDir) && is_writable($publicDir) && !file_exists($htaccessPath)) {
            @file_put_contents($htaccessPath, "<FilesMatch \"\\.(php|php8|phtml|phar|sh|pl|py|cgi)$\">\n    Require all denied\n</FilesMatch>\nOptions -Indexes\n");
        }

        // If public/uploads is writable, move there and also copy to storage/app/public/uploads
        if (is_dir($publicDir) && is_writable($publicDir)) {
            try {
                $file->move($publicDir, $fileName);
                if (is_dir($storageDir) && is_writable($storageDir)) {
                    @copy($publicDir . '/' . $fileName, $storageDir . '/' . $fileName);
                }
                return '/uploads/' . $fileName;
            } catch (\Throwable $e) {
                // Fallback below
            }
        }

        // 2. Fallback: write to storage/app/public/uploads (always writable by php-fpm)
        try {
            $file->move($storageDir, $fileName);
            if (is_dir($publicDir) && is_writable($publicDir)) {
                @copy($storageDir . '/' . $fileName, $publicDir . '/' . $fileName);
            }
            return '/uploads/' . $fileName;
        } catch (\Throwable $e) {
            error_log('[UPLOAD FALLBACK ERROR]: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Serve uploaded file from public/uploads or storage/app/public/uploads fallback
     */
    public function serveUpload(string $filename)
    {
        $safeName = basename($filename);
        $candidates = [
            public_path('uploads/' . $safeName),
            storage_path('app/public/uploads/' . $safeName),
        ];

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        abort(404);
    }
}
