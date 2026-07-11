<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return view('register');
    }

    public function doRegister(Request $request)
    {
        $skNumber = trim($request->sk_number);
        $email = trim($request->email);

        // Find if user already exists with the skNumber
        $existingBySk = User::where('sk_number', $skNumber)->where('role', 'TRANSLATOR')->first();

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
            'sk_number' => 'required|string|max:255',
            'email' => $emailRules,
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'sk_number.required' => 'Nomor Anggota wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($existingBySk) {
            $existingBySk->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'whatsapp' => $request->whatsapp ?: null,
            ]);
            $user = $existingBySk;
        } else {
            $user = User::create([
                'name' => $request->name,
                'sk_number' => $request->sk_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'TRANSLATOR',
                'whatsapp' => $request->whatsapp ?: null,
            ]);
        }

        Auth::login($user);

        return redirect('/admin');
    }

    public function checkMember($memberNo)
    {
        if (empty($memberNo)) {
            return response()->json(['success' => false, 'error' => 'Nomor anggota wajib diisi.'], 400);
        }

        $user = User::where('sk_number', $memberNo)->where('role', 'TRANSLATOR')->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'translator' => [
                    'name' => $user->name,
                    'email' => str_ends_with($user->email, '@ippti.or.id') ? '' : $user->email,
                    'whatsapp' => $user->whatsapp ?: '',
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
            'whatsapp' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        if ($user->role !== 'TRANSLATOR') {
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

        $updateData = [
            'email' => trim($request->email),
            'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : null,
            'bio' => $request->bio,
        ];

        // Prevent translator from editing their own name or language services (REV-11)
        if ($user->role !== 'TRANSLATOR') {
            $updateData['name'] = trim($request->name);
            $updateData['language_services'] = $request->language_services;
        }

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileName = 'profile-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            
            $file->move(public_path('uploads'), $fileName);
            $updateData['profile_picture'] = '/uploads/' . $fileName;
        }

        $before = $user->toArray();
        User::where('id', $user->id)->update($updateData);
        
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

        // Exclude Admin/Superadmin accounts from public search (REV-13)
        $queryBuilder = User::where('role', 'TRANSLATOR');

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
            ->firstOrFail();

        if ($translator->role !== 'TRANSLATOR') {
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
}
