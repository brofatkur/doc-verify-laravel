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

    // User management actions for Super Admin
    public function storeUser(Request $request)
    {
        if (Auth::user()->role !== 'SUPERADMIN') {
            return response()->json(['success' => false, 'error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'role' => 'required|in:TRANSLATOR,SUPERADMIN',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'sk_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $role = $request->role;
        $skNumber = trim($request->sk_number);
        if ($role === 'SUPERADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD';
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

        return redirect('/admin')->with('success', 'Akun pengguna baru berhasil dibuat!');
    }

    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'SUPERADMIN') {
            return response()->json(['success' => false, 'error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'role' => 'required|in:TRANSLATOR,SUPERADMIN',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'sk_number' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::findOrFail($id);

        $role = $request->role;
        $skNumber = trim($request->sk_number);
        if ($role === 'SUPERADMIN' && empty($skNumber)) {
            $skNumber = 'IPPTI-BOARD';
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

        $user->update($updateData);

        return redirect('/admin')->with('success', 'Data profil pengguna berhasil diperbarui!');
    }

    public function deleteUser($id)
    {
        if (Auth::user()->role !== 'SUPERADMIN') {
            return response()->json(['success' => false, 'error' => 'Akses ditolak.'], 403);
        }

        if (Auth::id() === $id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user = User::findOrFail($id);

        // Check if user has uploaded documents
        if (Document::where('translator_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Akun tidak bisa dihapus karena telah memiliki dokumen resmi terdaftar di sistem.']);
        }

        $user->delete();

        return redirect('/admin')->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
