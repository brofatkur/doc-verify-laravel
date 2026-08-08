@extends('layouts.app')

@section('title', 'Manajemen Voucher Diskon - DocVerify IPPTI')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 tracking-wide uppercase">
                        Admin Promotions
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        Unlimited & Expired Vouchers
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Manajemen Voucher Diskon</h1>
                <p class="text-slate-400 text-xs sm:text-sm">
                    Kelola kode voucher potongan harga top-up poin, kuota pemakaian, dan batas waktu aktif (unlimited atau berbatas waktu).
                </p>
            </div>

            <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-emerald-950/40 hover:shadow-emerald-900/60 transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Buat Voucher Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-700 p-4 rounded-xl text-xs sm:text-sm font-semibold flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/25 text-rose-700 p-4 rounded-xl text-xs sm:text-sm font-semibold space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                <span>Terdapat kendala input:</span>
            </div>
            <ul class="list-disc list-inside text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Voucher Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="ticket-check" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-slate-900">{{ $totalActive }}</h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Siap digunakan saat top-up</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Digunakan</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($totalUsedCount) }} Kali</h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Klaim diskon oleh penerjemah</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Terdaftar</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="tags" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-slate-900">{{ $totalVouchers }} Kode</h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Voucher aktif & nonaktif</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="/admin/vouchers" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative flex-1 max-w-sm">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode atau nama voucher..." class="w-full pl-9 pr-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold bg-slate-50/50">
                        <th class="py-3 px-4">Kode Voucher</th>
                        <th class="py-3 px-4">Nama & Deskripsi</th>
                        <th class="py-3 px-4">Nilai Diskon</th>
                        <th class="py-3 px-4">Min. Pembelian</th>
                        <th class="py-3 px-4">Penggunaan</th>
                        <th class="py-3 px-4">Masa Berlaku</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-black text-slate-900 px-2.5 py-1 bg-slate-100 rounded-lg border border-slate-200 tracking-wider">
                                        {{ $v->code }}
                                    </span>
                                    <button type="button" onclick="copyToClipboard('{{ $v->code }}')" title="Salin Kode" class="p-1 text-slate-400 hover:text-slate-700 rounded transition cursor-pointer">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 space-y-0.5">
                                <p class="font-bold text-slate-900">{{ $v->name ?: $v->code }}</p>
                                @if($v->description)
                                    <p class="text-[10px] text-slate-500 truncate max-w-xs">{{ $v->description }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($v->discount_type === 'PERCENTAGE')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                        <i data-lucide="percent" class="w-3 h-3"></i> Diskon {{ (int)$v->discount_value }}%
                                    </span>
                                    @if($v->max_discount_amount)
                                        <p class="text-[10px] text-slate-500 mt-0.5">Maks. Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}</p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                        Potongan Rp {{ number_format($v->discount_value, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                @if($v->min_order_amount > 0)
                                    Rp {{ number_format($v->min_order_amount, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">Tanpa Minimum</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900">{{ $v->used_count }}</span>
                                <span class="text-slate-400">/ {{ $v->usage_limit ? $v->usage_limit . ' kali' : 'Unlimited' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($v->is_unlimited_expiry || is_null($v->expires_at))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                                        <i data-lucide="infinity" class="w-3 h-3"></i> Unlimited (Permanen)
                                    </span>
                                @else
                                    @if($v->isExpired())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Kadaluarsa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700" title="{{ $v->expires_at->format('d M Y H:i') }}">
                                            <i data-lucide="clock" class="w-3 h-3"></i> s/d {{ $v->expires_at->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="/admin/vouchers/{{ $v->id }}/toggle" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold transition cursor-pointer {{ $v->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $v->is_active ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                        <span>{{ $v->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick='openEditModal(@json($v))' title="Edit Voucher" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form action="/admin/vouchers/{{ $v->id }}/delete" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher {{ $v->code }}?')">
                                        @csrf
                                        <button type="submit" title="Hapus Voucher" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 font-medium">
                                Belum ada voucher diskon yang dibuat. Klik tombol "+ Buat Voucher Baru" untuk membuat promo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $vouchers->links() }}
        </div>
    </div>
</div>

<!-- Modal Create / Edit Voucher -->
<div id="voucher-modal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 sm:p-7 border border-slate-200 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="space-y-0.5">
                <h3 id="modal-title" class="text-lg font-extrabold text-slate-900">Buat Voucher Diskon Baru</h3>
                <p class="text-xs text-slate-500">Atur kode promo, besaran potongan harga, dan batas waktu expired.</p>
            </div>
            <button type="button" onclick="closeVoucherModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="voucher-form" action="/admin/vouchers" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                    <input type="text" id="input-code" name="code" required class="w-full px-3.5 py-2 text-xs font-mono font-bold uppercase rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: DISKON50 / MERDEKA" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Promo / Kampanye</label>
                    <input type="text" id="input-name" name="name" class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: Promo Spesial Merdeka" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Catatan Syarat & Ketentuan</label>
                <textarea id="input-description" name="description" rows="2" class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Potongan harga khusus anggota IPPTI untuk topup poin verifikasi."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Diskon <span class="text-rose-500">*</span></label>
                    <select id="input-discount-type" name="discount_type" onchange="toggleDiscountType(this.value)" class="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:outline-none">
                        <option value="PERCENTAGE">Persentase (%)</option>
                        <option value="FIXED">Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label id="label-discount-value" class="block text-xs font-bold text-slate-700 mb-1">Besaran Persentase (%) <span class="text-rose-500">*</span></label>
                    <input type="number" id="input-discount-value" name="discount_value" required min="1" step="1" class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: 10 / 50000" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div id="wrapper-max-discount">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Maksimal Potongan Rupiah</label>
                    <input type="number" id="input-max-discount" name="max_discount_amount" min="0" step="1000" class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Kosongkan jika tanpa batas" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Minimal Pembelian (IDR)</label>
                    <input type="number" id="input-min-order" name="min_order_amount" min="0" step="1000" class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: 100000 (0 = tanpa min)" />
                </div>
            </div>

            <!-- Expiration Settings (Unlimited vs Scheduled Expired) -->
            <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200/80 space-y-3">
                <label class="block text-xs font-extrabold text-amber-950 uppercase tracking-wide">Pengaturan Masa Berlaku (Expired)</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 bg-white rounded-xl border border-amber-200 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio" name="expiry_mode" id="mode-unlimited" value="unlimited" checked onchange="toggleExpiryMode('unlimited')" class="text-emerald-600 focus:ring-emerald-500" />
                        <div>
                            <p class="text-xs font-bold text-slate-900">Unlimited (Permanen)</p>
                            <p class="text-[10px] text-slate-500">Tidak pernah kadaluarsa</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-3 bg-white rounded-xl border border-amber-200 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio" name="expiry_mode" id="mode-scheduled" value="scheduled" onchange="toggleExpiryMode('scheduled')" class="text-emerald-600 focus:ring-emerald-500" />
                        <div>
                            <p class="text-xs font-bold text-slate-900">Batas Waktu Tertentu</p>
                            <p class="text-[10px] text-slate-500">Memiliki tanggal expired</p>
                        </div>
                    </label>
                </div>

                <div id="wrapper-expires-at" class="hidden pt-1">
                    <label class="block text-xs font-bold text-amber-950 mb-1">Tanggal & Waktu Kadaluarsa (Expired)</label>
                    <input type="datetime-local" id="input-expires-at" name="expires_at" class="w-full px-3.5 py-2 text-xs font-semibold rounded-xl border border-amber-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batas Kuota Pemakaian</label>
                    <input type="number" id="input-usage-limit" name="usage_limit" min="1" class="w-full px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Kosongkan jika unlimited" />
                </div>
                <div class="pt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="input-is-active" name="is_active" value="1" checked class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" />
                        <span class="text-xs font-bold text-slate-800">Aktifkan Voucher Langsung</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeVoucherModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-submit-voucher" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Voucher</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Kode voucher "' + text + '" berhasil disalin ke clipboard!');
        });
    }

    function toggleDiscountType(type) {
        const label = document.getElementById('label-discount-value');
        const maxWrapper = document.getElementById('wrapper-max-discount');
        if (type === 'PERCENTAGE') {
            label.innerHTML = 'Besaran Persentase (%) <span class="text-rose-500">*</span>';
            maxWrapper.classList.remove('hidden');
        } else {
            label.innerHTML = 'Besaran Potongan (Rp) <span class="text-rose-500">*</span>';
            maxWrapper.classList.add('hidden');
        }
    }

    function toggleExpiryMode(mode) {
        const expiresWrapper = document.getElementById('wrapper-expires-at');
        const expiresInput = document.getElementById('input-expires-at');
        if (mode === 'scheduled') {
            expiresWrapper.classList.remove('hidden');
            expiresInput.setAttribute('required', 'required');
        } else {
            expiresWrapper.classList.add('hidden');
            expiresInput.removeAttribute('required');
        }
    }

    function openCreateModal() {
        document.getElementById('modal-title').innerText = 'Buat Voucher Diskon Baru';
        document.getElementById('voucher-form').action = '/admin/vouchers';
        document.getElementById('input-code').value = '';
        document.getElementById('input-code').removeAttribute('readonly');
        document.getElementById('input-name').value = '';
        document.getElementById('input-description').value = '';
        document.getElementById('input-discount-type').value = 'PERCENTAGE';
        document.getElementById('input-discount-value').value = '';
        document.getElementById('input-max-discount').value = '';
        document.getElementById('input-min-order').value = '';
        document.getElementById('input-usage-limit').value = '';
        document.getElementById('mode-unlimited').checked = true;
        document.getElementById('input-is-active').checked = true;
        
        toggleDiscountType('PERCENTAGE');
        toggleExpiryMode('unlimited');
        document.getElementById('voucher-modal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditModal(voucher) {
        document.getElementById('modal-title').innerText = 'Edit Voucher: ' + voucher.code;
        document.getElementById('voucher-form').action = '/admin/vouchers/' + voucher.id + '/update';
        document.getElementById('input-code').value = voucher.code;
        document.getElementById('input-name').value = voucher.name || '';
        document.getElementById('input-description').value = voucher.description || '';
        document.getElementById('input-discount-type').value = voucher.discount_type;
        document.getElementById('input-discount-value').value = parseFloat(voucher.discount_value);
        document.getElementById('input-max-discount').value = voucher.max_discount_amount ? parseFloat(voucher.max_discount_amount) : '';
        document.getElementById('input-min-order').value = voucher.min_order_amount ? parseFloat(voucher.min_order_amount) : '';
        document.getElementById('input-usage-limit').value = voucher.usage_limit || '';
        document.getElementById('input-is-active').checked = voucher.is_active;

        if (voucher.is_unlimited_expiry || !voucher.expires_at) {
            document.getElementById('mode-unlimited').checked = true;
            toggleExpiryMode('unlimited');
        } else {
            document.getElementById('mode-scheduled').checked = true;
            toggleExpiryMode('scheduled');
            const d = new Date(voucher.expires_at);
            const iso = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            document.getElementById('input-expires-at').value = iso;
        }

        toggleDiscountType(voucher.discount_type);
        document.getElementById('voucher-modal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeVoucherModal() {
        document.getElementById('voucher-modal').classList.add('hidden');
    }
</script>
@endsection
