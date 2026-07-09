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
        $isAdmin = $user->role === 'ADMIN';

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
        $documentTypes = \App\Models\DocumentType::orderBy('name', 'asc')->get();
        $languageDirections = \App\Models\LanguageDirection::orderBy('name', 'asc')->get();
        return view('admin.new', compact('documentTypes', 'languageDirections'));
    }

    public function editDocument($id)
    {
        $user = Auth::user();
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
}
