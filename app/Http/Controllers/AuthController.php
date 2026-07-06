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
}
