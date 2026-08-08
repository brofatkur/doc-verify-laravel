<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use App\Models\AuditLog;

class VoucherController extends Controller
{
    /**
     * Display Vouchers Management Page
     */
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403, 'Akses Terbatas: Hanya Pengurus Admin IPPTI yang dapat mengelola Voucher Diskon.');
        }

        Voucher::ensureTableExists();

        $query = Voucher::query()->with('user');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $vouchers = $query->orderBy('id', 'desc')->paginate(15);

        $totalActive = Voucher::where('is_active', true)->count();
        $totalUsedCount = Voucher::sum('used_count');
        $totalVouchers = Voucher::count();

        return view('admin.vouchers', compact('vouchers', 'totalActive', 'totalUsedCount', 'totalVouchers'));
    }

    /**
     * Store a newly created Voucher
     */
    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        Voucher::ensureTableExists();

        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:PERCENTAGE,FIXED',
            'discount_value' => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expiry_mode' => 'required|in:unlimited,scheduled',
            'expires_at' => 'nullable|required_if:expiry_mode,scheduled|date',
        ], [
            'code.required' => 'Kode voucher wajib diisi.',
            'code.unique' => 'Kode voucher ini sudah digunakan. Harap gunakan kode unik lain.',
            'discount_value.required' => 'Besaran diskon wajib diisi.',
            'expires_at.required_if' => 'Tanggal kadaluarsa wajib diisi jika mode waktu terbatas dipilih.',
        ]);

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $request->code));
        $isUnlimited = $request->expiry_mode === 'unlimited';
        $expiresAt = $isUnlimited ? null : $request->expires_at;

        $voucher = Voucher::create([
            'code' => $code,
            'name' => $request->name ?: $code,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => (float)$request->discount_value,
            'min_order_amount' => (float)($request->min_order_amount ?: 0),
            'max_discount_amount' => $request->discount_type === 'PERCENTAGE' && $request->max_discount_amount ? (float)$request->max_discount_amount : null,
            'usage_limit' => $request->usage_limit ? (int)$request->usage_limit : null,
            'used_count' => 0,
            'is_unlimited_expiry' => $isUnlimited,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active') ? true : false,
            'created_by' => Auth::id(),
        ]);

        AuditLog::log('CREATE_VOUCHER', Voucher::class, $voucher->id, [], $voucher->toArray());

        return back()->with('success', "Voucher diskon \"{$voucher->code}\" berhasil dibuat!");
    }

    /**
     * Update an existing Voucher
     */
    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:PERCENTAGE,FIXED',
            'discount_value' => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expiry_mode' => 'required|in:unlimited,scheduled',
            'expires_at' => 'nullable|required_if:expiry_mode,scheduled|date',
        ]);

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $request->code));
        $isUnlimited = $request->expiry_mode === 'unlimited';
        $expiresAt = $isUnlimited ? null : $request->expires_at;

        $oldData = $voucher->toArray();

        $voucher->update([
            'code' => $code,
            'name' => $request->name ?: $code,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => (float)$request->discount_value,
            'min_order_amount' => (float)($request->min_order_amount ?: 0),
            'max_discount_amount' => $request->discount_type === 'PERCENTAGE' && $request->max_discount_amount ? (float)$request->max_discount_amount : null,
            'usage_limit' => $request->usage_limit ? (int)$request->usage_limit : null,
            'is_unlimited_expiry' => $isUnlimited,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        AuditLog::log('UPDATE_VOUCHER', Voucher::class, $voucher->id, $oldData, $voucher->toArray());

        return back()->with('success', "Voucher diskon \"{$voucher->code}\" berhasil diperbarui!");
    }

    /**
     * Toggle Voucher active / inactive status
     */
    public function toggleActive($id)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);
        $voucher->update(['is_active' => !$voucher->is_active]);

        $statusText = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::log('TOGGLE_VOUCHER_STATUS', Voucher::class, $voucher->id, [], ['is_active' => $voucher->is_active]);

        return back()->with('success', "Voucher \"{$voucher->code}\" berhasil {$statusText}.");
    }

    /**
     * Delete a voucher
     */
    public function destroy($id)
    {
        if (!in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);
        $code = $voucher->code;
        $voucher->delete();

        AuditLog::log('DELETE_VOUCHER', Voucher::class, $id, [], ['code' => $code]);

        return back()->with('success', "Voucher \"{$code}\" telah dihapus.");
    }

    /**
     * AJAX Endpoint: Check and Validate Voucher Code for Topup / Upgrade
     */
    public function checkVoucher(Request $request)
    {
        Voucher::ensureTableExists();

        $code = strtoupper(trim((string)$request->input('code', '')));
        $amount = (float)$request->input('amount', 0);

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan kode voucher diskon.',
            ], 400);
        }

        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher "' . $code . '" tidak ditemukan.',
            ], 404);
        }

        $validation = $voucher->isValidForAmount($amount);

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (float)$voucher->discount_value,
            'discount_amount' => (float)$validation['discount_amount'],
            'original_amount' => $amount,
            'final_amount' => (float)$validation['final_amount'],
            'message' => $validation['message'],
        ]);
    }
}
