<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Document;

class DocumentController extends Controller
{
    private function generateDocumentId()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Document::where("document_id", $code)->exists());

        return $code;
    }

    private function generateRegNumber()
    {
        return mt_rand(100000, 900000) . substr(time(), -4);
    }

    private function normalizeKey($key)
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim((string)$key)));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(
                ["success" => false, "error" => "Akses ditolak."],
                403,
            );
        }

        $request->validate([
            "registration_number" => "nullable|string|max:255",
            "document_date" => "required|date",
            "document_type" => "required|string|max:255",
            "language_pair" => "required|string|max:255",
            "client_name" => "required|string|max:255",
        ]);

        $regNumber = $request->filled("registration_number")
            ? trim($request->registration_number)
            : $this->generateRegNumber();

        // Check if registration number already exists for this translator (REV-08 rule)
        $existsSameTranslator = Document::where('registration_number', $regNumber)
            ->where('translator_id', $user->id)
            ->exists();

        if ($existsSameTranslator) {
            return back()->withErrors(['registration_number' => 'Nomor registrasi sudah terdaftar untuk akun Penerjemah Anda. Silakan ubah secara manual.'])->withInput();
        }

        // Auto-create missing master data
        \App\Models\DocumentType::firstOrCreate(['name' => trim($request->document_type)]);
        \App\Models\LanguageDirection::firstOrCreate(['name' => trim($request->language_pair)]);

        $documentId = $this->generateDocumentId();

        $doc = Document::create([
            "document_id" => $documentId,
            "registration_number" => $regNumber,
            "document_date" => $request->document_date,
            "document_type" => trim($request->document_type),
            "language_pair" => trim($request->language_pair),
            "client_name" => trim($request->client_name),
            "status" => "Selesai",
            "is_qr_generated" => true,
            "translator_id" => $user->id,
        ]);

        \App\Models\AuditLog::log('CREATE_DOCUMENT', Document::class, $doc->id, null, $doc->toArray());

        return redirect("/admin")->with(
            "success",
            "Dokumen terjemahan baru berhasil disimpan!",
        );
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $document = Document::findOrFail($id);

        if ($document->translator_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda bukan pemilik dokumen ini.');
        }

        $request->validate([
            "registration_number" => "nullable|string|max:255",
            "document_date" => "required|date",
            "document_type" => "required|string|max:255",
            "language_pair" => "required|string|max:255",
            "client_name" => "required|string|max:255",
        ]);

        $regNumber = $request->filled("registration_number")
            ? trim($request->registration_number)
            : $document->registration_number;

        // Check if registration number already exists for this translator (REV-08 rule)
        $existsSameTranslator = Document::where('registration_number', $regNumber)
            ->where('translator_id', $user->id)
            ->where('id', '!=', $id)
            ->exists();

        if ($existsSameTranslator) {
            return back()->withErrors(['registration_number' => 'Nomor registrasi sudah terdaftar untuk akun Penerjemah Anda. Silakan ubah secara manual.'])->withInput();
        }

        // Auto-create missing master data
        \App\Models\DocumentType::firstOrCreate(['name' => trim($request->document_type)]);
        \App\Models\LanguageDirection::firstOrCreate(['name' => trim($request->language_pair)]);

        $before = $document->toArray();

        $document->update([
            "registration_number" => $regNumber,
            "document_date" => $request->document_date,
            "document_type" => trim($request->document_type),
            "language_pair" => trim($request->language_pair),
            "client_name" => trim($request->client_name),
        ]);

        $after = $document->toArray();

        \App\Models\AuditLog::log('UPDATE_DOCUMENT', Document::class, $document->id, $before, $after);

        return redirect("/admin")->with(
            "success",
            "Dokumen terjemahan berhasil diperbarui!"
        );
    }

    public function archive($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $document = Document::findOrFail($id);

        if ($document->translator_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda bukan pemilik dokumen ini.');
        }

        $before = $document->toArray();
        $document->delete(); // Soft delete

        \App\Models\AuditLog::log('ARCHIVE_DOCUMENT', Document::class, $document->id, $before, null);

        return redirect("/admin")->with(
            "success",
            "Dokumen terjemahan berhasil diarsipkan!"
        );
    }

    public function toggleQr(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(
                ["success" => false, "error" => "Akses ditolak."],
                403,
            );
        }

        $query = Document::withTrashed()->where("id", $id);
        if ($user->role !== "SUPERADMIN") {
            $query->where("translator_id", $user->id);
        }

        $doc = $query->first();
        if (!$doc) {
            return response()->json(
                ["success" => false, "error" => "Dokumen tidak ditemukan."],
                404,
            );
        }

        $before = $doc->toArray();
        $doc->update([
            "is_qr_generated" => !$doc->is_qr_generated,
        ]);
        $after = $doc->toArray();

        \App\Models\AuditLog::log('TOGGLE_QR', Document::class, $doc->id, $before, $after);

        return response()->json(["success" => true]);
    }

    public function importJson(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(
                ["success" => false, "error" => "Akses ditolak."],
                403,
            );
        }

        $rows = $request->input("rows");
        if (!is_array($rows) || empty($rows)) {
            return response()->json(
                ["success" => false, "error" => "Data impor kosong."],
                400,
            );
        }

        // Validasi kolom sesuai template
        $firstRow = $rows[0];
        $headers = array_keys($firstRow);
        $hasClientName = false;
        $hasDocType = false;
        $hasLangPair = false;

        foreach ($headers as $header) {
            $h = $this->normalizeKey($header);
            if (in_array($h, ['namaklien', 'namadidokumen', 'clientname', 'klien'])) {
                $hasClientName = true;
            }
            if (in_array($h, ['tipedokumen', 'documenttype', 'tipe'])) {
                $hasDocType = true;
            }
            if (in_array($h, ['arahbahasa', 'pasanganbahasa', 'languagepair', 'bahasa'])) {
                $hasLangPair = true;
            }
        }

        if (!$hasClientName || !$hasDocType || !$hasLangPair) {
            return response()->json([
                'success' => false,
                'error' => 'Format kolom tidak sesuai template. Pastikan file memiliki kolom: Nama di Dokumen, Tipe Dokumen, dan Pasangan Bahasa.'
            ], 400);
        }

        $importedCount = 0;

        // Transactional execution (all-or-nothing, REV-22)
        \DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Normalize row keys
                $normalizedRow = [];
                foreach ($row as $k => $v) {
                    $normalizedRow[$this->normalizeKey($k)] = $v;
                }

                $regNum = isset($normalizedRow['noregister']) ? trim((string)$normalizedRow['noregister']) : (
                    isset($normalizedRow['noregistrasi']) ? trim((string)$normalizedRow['noregistrasi']) : (
                        isset($normalizedRow['nomorregistrasi']) ? trim((string)$normalizedRow['nomorregistrasi']) : (
                            isset($normalizedRow['registrationnumber']) ? trim((string)$normalizedRow['registrationnumber']) : ''
                        )
                    )
                );
                if (empty($regNum)) {
                    $regNum = $this->generateRegNumber();
                }

                $rawDate = $normalizedRow['tanggal'] ?? (
                    $normalizedRow['tanggaldokumen'] ?? (
                        $normalizedRow['date'] ?? (
                            $normalizedRow['documentdate'] ?? date('Y-m-d')
                        )
                    )
                );

                $docDate = date(
                    "Y-m-d",
                    is_numeric($rawDate)
                        ? ($rawDate - 25569) * 86400
                        : strtotime(str_replace("/", "-", (string)$rawDate)),
                );

                $docType = isset($normalizedRow['tipedokumen']) ? trim((string)$normalizedRow['tipedokumen']) : (
                    isset($normalizedRow['documenttype']) ? trim((string)$normalizedRow['documenttype']) : (
                        isset($normalizedRow['tipe']) ? trim((string)$normalizedRow['tipe']) : ''
                    )
                );
                
                $langPair = isset($normalizedRow['arahbahasa']) ? trim((string)$normalizedRow['arahbahasa']) : (
                    isset($normalizedRow['pasanganbahasa']) ? trim((string)$normalizedRow['pasanganbahasa']) : (
                        isset($normalizedRow['languagepair']) ? trim((string)$normalizedRow['languagepair']) : (
                            isset($normalizedRow['bahasa']) ? trim((string)$normalizedRow['bahasa']) : ''
                        )
                    )
                );
                
                $clientName = isset($normalizedRow['namaklien']) ? trim((string)$normalizedRow['namaklien']) : (
                    isset($normalizedRow['namadidokumen']) ? trim((string)$normalizedRow['namadidokumen']) : (
                        isset($normalizedRow['clientname']) ? trim((string)$normalizedRow['clientname']) : (
                            isset($normalizedRow['klien']) ? trim((string)$normalizedRow['klien']) : ''
                        )
                    )
                );

                $qrRaw = $normalizedRow['buatkodeqrverifikasi'] ?? (
                    $normalizedRow['isqrgenerated'] ?? 'Ya'
                );
                $generateQr = is_bool($qrRaw)
                    ? $qrRaw
                    : strtolower(trim((string)$qrRaw)) === "ya";

                // Server-side row validation (REV-22)
                $rowNum = $index + 1;
                if (empty($clientName)) {
                    throw new \Exception("Baris {$rowNum}: Nama Klien / Nama di Dokumen wajib diisi.");
                }
                if (empty($docType)) {
                    throw new \Exception("Baris {$rowNum}: Tipe Dokumen wajib diisi.");
                }
                if (empty($langPair)) {
                    throw new \Exception("Baris {$rowNum}: Arah / Pasangan Bahasa wajib diisi.");
                }
                // Check if registration number already exists for this translator (REV-08 rule)
                $existsSameTranslator = Document::where('registration_number', $regNum)
                    ->where('translator_id', $user->id)
                    ->exists();

                if ($existsSameTranslator) {
                    throw new \Exception("Baris {$rowNum}: Nomor registrasi '{$regNum}' sudah terdaftar untuk akun Penerjemah Anda. Silakan ubah secara manual.");
                }

                $docId = $this->generateDocumentId();

                $insertData = [
                    "document_id" => $docId,
                    "registration_number" => $regNum,
                    "document_date" => $docDate,
                    "document_type" => $docType,
                    "language_pair" => $langPair,
                    "client_name" => $clientName,
                    "status" => "Selesai",
                    "is_qr_generated" => true,
                    "translator_id" => $user->id,
                ];

                $doc = Document::create($insertData);
                
                // Audit logging (REV-12)
                \App\Models\AuditLog::log('IMPORT_DOCUMENT', Document::class, $doc->id, null, $insertData);

                $importedCount++;
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ], 400);
        }

        return response()->json([
            "success" => true,
            "importedCount" => $importedCount,
            "skippedCount" => 0,
            "errors" => []
        ]);
    }

    public function showPublicVerify($documentId)
    {
        if (strlen($documentId) !== 8) {
            abort(404);
        }

        $document = Document::withTrashed()
            ->where("document_id", $documentId)
            ->with([
                "translator" => function ($query) {
                    $query->select(
                        "id",
                        "name",
                        "sk_number",
                        "bio",
                        "language_services",
                        "profile_picture",
                        "role"
                    );
                },
            ])
            ->first();

        // Exclude admin/superadmin accounts from public verification results (REV-13)
        if ($document && $document->translator && $document->translator->role !== 'TRANSLATOR') {
            $document = null;
        }

        return view("verify", compact("document", "documentId"));
    }

    public function search(Request $request)
    {
        $query = trim($request->input("query"));
        if (empty($query)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(["error" => "Silakan masukkan nomor registrasi atau ID Dokumen."], 400);
            }
            return back()->with(
                "error",
                "Silakan masukkan nomor registrasi atau ID Dokumen.",
            );
        }

        // Endpoint security: require minimal characters in queries (REV-19)
        if (strlen($query) < 3) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(["error" => "Kata kunci pencarian terlalu pendek. Masukkan minimal 3 karakter."], 400);
            }
            return back()->with(
                "error",
                "Kata kunci pencarian terlalu pendek. Masukkan minimal 3 karakter.",
            );
        }

        // 1. First attempt to match exact Document ID (8 characters)
        $docById = Document::withTrashed()
            ->where("document_id", $query)
            ->with('translator')
            ->first();

        if ($docById) {
            if ($docById->translator && $docById->translator->role !== 'TRANSLATOR') {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(["error" => "Dokumen terverifikasi tidak ditemukan."], 404);
                }
                return back()->with("error", "Dokumen terverifikasi tidak ditemukan.");
            }
            if (!$docById->is_qr_generated) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(["error" => "Dokumen ini belum diotorisasi untuk verifikasi publik."], 403);
                }
                return back()->with("error", "Dokumen ini belum diotorisasi untuk verifikasi publik.");
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(["multiple" => false, "redirect" => "/verify/" . $docById->document_id]);
            }
            return redirect("/verify/" . $docById->document_id);
        }

        // 2. Otherwise match by Registration Number, Document Owner Name (client_name), or Document ID
        $docs = Document::withTrashed()
            ->where("is_qr_generated", true)
            ->whereHas('translator', function ($q) {
                $q->where('role', 'TRANSLATOR');
            })
            ->where(function ($q) use ($query) {
                $q->where("registration_number", $query)
                  ->orWhere("registration_number", "LIKE", "%{$query}%")
                  ->orWhere("client_name", "LIKE", "%{$query}%")
                  ->orWhere("document_id", "LIKE", "%{$query}%");
            })
            ->with('translator')
            ->get();

        if ($docs->isEmpty()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(["error" => "Dokumen terverifikasi tidak ditemukan."], 404);
            }
            return back()->with(
                "error",
                "Dokumen terverifikasi tidak ditemukan.",
            );
        }

        if ($docs->count() === 1) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(["multiple" => false, "redirect" => "/verify/" . $docs->first()->document_id]);
            }
            return redirect("/verify/" . $docs->first()->document_id);
        }

        // Return JSON payload for popup modal if AJAX (REV-08)
        if ($request->expectsJson() || $request->ajax()) {
            $formattedDocs = $docs->map(function ($doc) {
                $words = explode(' ', $doc->client_name);
                $maskedName = implode(' ', array_map(function($w) {
                    return strlen($w) <= 1 ? $w : $w[0] . str_repeat('*', strlen($w) - 1);
                }, $words));

                return [
                    'document_id' => $doc->document_id,
                    'document_type' => $doc->document_type,
                    'language_pair' => $doc->language_pair,
                    'client_name' => $maskedName,
                    'document_date' => $doc->document_date ? $doc->document_date->format('d M Y') : '-',
                    'translator' => [
                        'name' => $doc->translator->name,
                        'sk_number' => $doc->translator->sk_number,
                        'profile_picture' => $doc->translator->profile_picture,
                    ]
                ];
            });

            return response()->json([
                'multiple' => true,
                'documents' => $formattedDocs,
                'regNumber' => $query
            ]);
        }

        // 3. Fallback for non-JS / standard form submissions (Disambiguation page)
        $regNumber = $query;
        $documents = $docs;
        return view("verify-disambiguation", compact("documents", "regNumber"));
    }
}
