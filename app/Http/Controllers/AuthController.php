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
        $request->validate([
            'name' => 'required|string|max:255',
            'sk_number' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
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

        $user = User::create([
            'name' => $request->name,
            'sk_number' => $request->sk_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'TRANSLATOR', // register is only for sworn translators
        ]);

        Auth::login($user);

        return redirect('/admin');
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

        $request->validate([
            'language_services' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'profile_picture.image' => 'Berkas harus berupa gambar.',
            'profile_picture.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'profile_picture.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $updateData = [
            'language_services' => $request->language_services,
            'bio' => $request->bio,
        ];

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileName = 'profile-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            
            // Move file directly to public/uploads directory for simple deployment compatibility
            $file->move(public_path('uploads'), $fileName);
            $updateData['profile_picture'] = '/uploads/' . $fileName;
        }

        // Use direct update to avoid missing dynamic models attributes validation
        User::where('id', $user->id)->update($updateData);

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
        
        $cleanQuery = strtolower(trim($query));
        
        $translators = User::where('sk_number', '!=', 'IPPTI-HQ-2026')
            ->where('sk_number', '!=', 'IPPTI-ADMIN-01')
            ->where(function ($q) use ($cleanQuery) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$cleanQuery}%"])
                  ->orWhereRaw('LOWER(sk_number) LIKE ?', ["%{$cleanQuery}%"])
                  ->orWhereRaw('LOWER(bio) LIKE ?', ["%{$cleanQuery}%"]);
            })
            ->select('id', 'name', 'sk_number', 'language_services', 'profile_picture')
            ->get();
            
        return response()->json(['success' => true, 'translators' => $translators]);
    }

    public function showPublicTranslator($translatorId)
    {
        $translator = User::where('id', $translatorId)
            ->orWhere('sk_number', $translatorId)
            ->firstOrFail();

        if ($translator->sk_number === 'IPPTI-HQ-2026' || $translator->sk_number === 'IPPTI-ADMIN-01') {
            abort(404);
        }

        return view('verify-translator', compact('translator'));
    }
}
