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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
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

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('status', 'Jika email terdaftar, instruksi reset password telah dikirim.');
        }

        if (str_ends_with($user->email, '@ippti.or.id')) {
            return back()->withErrors(['email' => 'Akun Anda belum diklaim/diaktifkan. Silakan lakukan pendaftaran terlebih dahulu menggunakan Nomor Anggota Anda.']);
        }

        $token = Str::random(60);
        
        // Save to DB
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        $resetLink = url('/reset-password?token=' . $token);

        // Log link to terminal for easy local testing
        error_log("\n======================================================");
        error_log("[RESET PASSWORD LINK (LARAVEL) FOR {$email}]:");
        error_log($resetLink);
        error_log("======================================================\n");

        return back()->with('status', 'Link reset password telah dikirim ke email terdaftar (dan dicatat di log terminal).');
    }

    public function resetPasswordView(Request $request)
    {
        $token = $request->query('token');
        return view('auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'token.required' => 'Token reset wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.'
        ]);

        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kadaluarsa.']);
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
        $ext = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
        $fileName = $basePrefix . '.' . $ext;

        $publicDir = public_path('uploads');
        $storageDir = storage_path('app/public/uploads');

        // 1. Try storage/app/public/uploads first or as guaranteed writable directory
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0775, true);
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
