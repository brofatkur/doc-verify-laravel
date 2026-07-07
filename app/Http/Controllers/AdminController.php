<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Document;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $isSuperAdmin = $user->role === 'SUPERADMIN';

        if ($isSuperAdmin) {
            $translators = User::withCount('documents')->orderBy('name', 'asc')->get();
            $documents = Document::with('translator')->orderBy('created_at', 'desc')->get();
            
            $totalTranslators = $translators->count();
            $totalDocs = $documents->count();
            $totalQrCodes = $documents->where('is_qr_generated', true)->count();

            return view('admin.dashboard', compact(
                'isSuperAdmin', 'translators', 'documents', 
                'totalTranslators', 'totalDocs', 'totalQrCodes'
            ));
        }

        // Sworn Translator Dashboard view
        $documents = Document::where('translator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalDocs = $documents->count();
        $totalQrCodes = $documents->where('is_qr_generated', true)->count();

        return view('admin.dashboard', compact('isSuperAdmin', 'documents', 'totalDocs', 'totalQrCodes'));
    }

    public function createDocument()
    {
        return view('admin.new');
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

        User::create([
            'role' => $role,
            'name' => $request->name,
            'email' => $request->email,
            'sk_number' => $skNumber,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/admin/users')->with('success', 'Akun pengguna baru berhasil dibuat!');
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
            'sk_number' => 'nullable|string|max:255',
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

        $updateData = [
            'role' => $role,
            'name' => $request->name,
            'email' => $request->email,
            'sk_number' => $skNumber,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $targetUser->update($updateData);

        return redirect('/admin/users')->with('success', 'Data profil pengguna berhasil diperbarui!');
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

            // Check duplicate
            $existing = User::where('email', $email)
                ->orWhere('sk_number', $noAnggota)
                ->first();

            if ($existing) {
                $skippedCount++;
                $errors[] = "Penerjemah '{$nama}' ({$email}/{$noAnggota}) sudah terdaftar, dilewati.";
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
}
