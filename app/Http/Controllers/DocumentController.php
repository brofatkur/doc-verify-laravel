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

        // Check unique
        if (Document::where("registration_number", $regNumber)->exists()) {
            return back()
                ->withErrors([
                    "registration_number" => "Nomor registrasi '{$regNumber}' sudah terdaftar.",
                ])
                ->withInput();
        }

        $documentId = $this->generateDocumentId();

        Document::create([
            "document_id" => $documentId,
            "registration_number" => $regNumber,
            "document_date" => $request->document_date,
            "document_type" => $request->document_type,
            "language_pair" => $request->language_pair,
            "client_name" => $request->client_name,
            "status" => "Selesai",
            "is_qr_generated" => $request->has("is_qr_generated"),
            "translator_id" => $user->id,
        ]);

        return redirect("/admin")->with(
            "success",
            "Dokumen terjemahan baru berhasil disimpan!",
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

        $query = Document::where("id", $id);
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

        $doc->update([
            "is_qr_generated" => !$doc->is_qr_generated,
        ]);

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
        $skippedCount = 0;
        $errors = [];

        foreach ($rows as $row) {
            // Normalize row keys
            $normalizedRow = [];
            foreach ($row as $k => $v) {
                $normalizedRow[$this->normalizeKey($k)] = $v;
            }

            // Find key variants in Excel file columns
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

            // Format dates
            $docDate = date(
                "Y-m-d",
                is_numeric($rawDate)
                    ? ($rawDate - 25569) * 86400
                    : strtotime(str_replace("/", "-", (string)$rawDate)),
            );

            $docType = isset($normalizedRow['tipedokumen']) ? trim((string)$normalizedRow['tipedokumen']) : (
                isset($normalizedRow['documenttype']) ? trim((string)$normalizedRow['documenttype']) : (
                    isset($normalizedRow['tipe']) ? trim((string)$normalizedRow['tipe']) : 'Dokumen Terjemahan'
                )
            );
            
            $langPair = isset($normalizedRow['arahbahasa']) ? trim((string)$normalizedRow['arahbahasa']) : (
                isset($normalizedRow['pasanganbahasa']) ? trim((string)$normalizedRow['pasanganbahasa']) : (
                    isset($normalizedRow['languagepair']) ? trim((string)$normalizedRow['languagepair']) : (
                        isset($normalizedRow['bahasa']) ? trim((string)$normalizedRow['bahasa']) : 'N/A'
                    )
                )
            );
            
            $clientName = isset($normalizedRow['namaklien']) ? trim((string)$normalizedRow['namaklien']) : (
                isset($normalizedRow['namadidokumen']) ? trim((string)$normalizedRow['namadidokumen']) : (
                    isset($normalizedRow['clientname']) ? trim((string)$normalizedRow['clientname']) : (
                        isset($normalizedRow['klien']) ? trim((string)$normalizedRow['klien']) : 'N/A'
                    )
                )
            );

            // Handle QR verification flag
            $qrRaw = $normalizedRow['buatkodeqrverifikasi'] ?? (
                $normalizedRow['isqrgenerated'] ?? 'Ya'
            );
            $generateQr = is_bool($qrRaw)
                ? $qrRaw
                : strtolower(trim((string)$qrRaw)) === "ya";

            try {
                if (Document::where("registration_number", $regNum)->exists()) {
                    $skippedCount++;
                    $errors[] = "Nomor registrasi '{$regNum}' sudah terdaftar, baris dilewati.";
                    continue;
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
                    "is_qr_generated" => $generateQr,
                    "translator_id" => $user->id,
                ];

                Document::create($insertData);

                $importedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] =
                    "Gagal mengimpor '{$clientName}': " . $e->getMessage();
            }
        }

        return response()->json([
            "success" => true,
            "importedCount" => $importedCount,
            "skippedCount" => $skippedCount,
            "errors" => $errors,
        ]);
    }

    public function showPublicVerify($documentId)
    {
        if (strlen($documentId) !== 8) {
            abort(404);
        }

        $document = Document::where("document_id", $documentId)
            ->with([
                "translator" => function ($query) {
                    $query->select(
                        "id",
                        "name",
                        "sk_number",
                        "bio",
                        "language_services",
                        "profile_picture",
                    );
                },
            ])
            ->first();

        // Pass document to view (if null or not qr generated, the view displays the "not found" container)
        return view("verify", compact("document", "documentId"));
    }

    public function search(Request $request)
    {
        $query = trim($request->input("query"));
        if (empty($query)) {
            return back()->with(
                "error",
                "Silakan masukkan nomor registrasi atau ID Dokumen.",
            );
        }

        $doc = Document::where("document_id", $query)
            ->orWhere("registration_number", $query)
            ->first();

        if (!$doc) {
            return back()->with(
                "error",
                "Dokumen terverifikasi tidak ditemukan.",
            );
        }

        if (!$doc->is_qr_generated) {
            return back()->with(
                "error",
                "Dokumen ini belum diotorisasi untuk verifikasi publik.",
            );
        }

        return redirect("/verify/" . $doc->document_id);
    }
}
