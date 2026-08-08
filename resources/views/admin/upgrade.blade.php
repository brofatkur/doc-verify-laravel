@extends('layouts.app')

@section('title', 'DocVerify IPPTI - Top-Up Poin & Aktivasi Mode PRO')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 py-4">
    <div class="flex items-center gap-3">
        <a href="/admin" class="p-2 hover:bg-slate-200 rounded-xl transition text-slate-500">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->isPro())
                    <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-600"></i> AKUN PRO AKTIF
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                        STATUS SEKARANG: REGULER
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Top-Up Poin & Aktivasi Mode PRO</h1>
            <p class="text-slate-500 text-sm">Bebas biaya awal aktivasi! 100% nominal top-up langsung menjadi saldo Poin Verifikasi.</p>
        </div>
    </div>

    <!-- Info Banner: Zero Activation Fee -->
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-950 p-4 rounded-2xl flex items-start gap-3 shadow-xs">
        <div class="p-2 bg-emerald-600 text-white rounded-xl flex-shrink-0 mt-0.5">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-xs font-extrabold text-emerald-950 uppercase tracking-wide">Bebas Biaya Awal Aktivasi Mode PRO</h3>
            <p class="text-xs text-emerald-900/90 leading-relaxed">
                Anda tidak perlu membayar biaya pendaftaran/aktivasi terpisah. Cukup lakukan top-up minimal <strong class="font-mono text-emerald-700 font-bold">Rp {{ number_format($minTopup, 0, ',', '.') }}</strong>, seluruh nominal pembayaran akan otomatis menjadi saldo Poin dan akun Anda langsung aktif sebagai <strong class="text-amber-700 font-extrabold">AKUN PRO</strong> secara permanen.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 md:p-8 space-y-6">
            
            <!-- Step 1: Select Preset Nominal -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                    <span>Pilih Nominal Top-Up Poin:</span>
                    <span class="text-[11px] text-slate-400 font-normal">Minimal: Rp {{ number_format($minTopup, 0, ',', '.') }}</span>
                </label>

                <!-- Grid Preset Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button type="button" onclick="selectNominal(100000)" id="preset-100000" class="preset-btn p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 text-left transition relative cursor-pointer group">
                        <span class="absolute -top-2.5 right-3 bg-emerald-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">Minimal PRO</span>
                        <p class="text-xs font-extrabold text-slate-500">Poin: 100.000</p>
                        <p class="text-base font-black text-slate-900 font-mono mt-0.5">Rp 100.000</p>
                    </button>

                    <button type="button" onclick="selectNominal(200000)" id="preset-200000" class="preset-btn p-4 rounded-2xl border-2 border-slate-200 bg-white text-left transition hover:border-slate-300 cursor-pointer group">
                        <p class="text-xs font-extrabold text-slate-500">Poin: 200.000</p>
                        <p class="text-base font-black text-slate-900 font-mono mt-0.5">Rp 200.000</p>
                    </button>

                    <button type="button" onclick="selectNominal(500000)" id="preset-500000" class="preset-btn p-4 rounded-2xl border-2 border-slate-200 bg-white text-left transition hover:border-slate-300 cursor-pointer group">
                        <p class="text-xs font-extrabold text-slate-500">Poin: 500.000</p>
                        <p class="text-base font-black text-slate-900 font-mono mt-0.5">Rp 500.000</p>
                    </button>

                    <button type="button" onclick="selectNominal(1000000)" id="preset-1000000" class="preset-btn p-4 rounded-2xl border-2 border-slate-200 bg-white text-left transition hover:border-slate-300 cursor-pointer group">
                        <p class="text-xs font-extrabold text-slate-500">Poin: 1.000.000</p>
                        <p class="text-base font-black text-slate-900 font-mono mt-0.5">Rp 1.000.000</p>
                    </button>
                </div>
            </div>

            <!-- Custom Amount Option -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                <label for="custom_amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                    <span>Atau Masukkan Nominal Lainnya (Rp):</span>
                    <span class="text-[10px] text-slate-400 font-semibold font-mono">Min. Rp {{ number_format($minTopup, 0, ',', '.') }}</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">Rp</span>
                    <input
                        type="number"
                        id="custom_amount"
                        name="custom_amount"
                        placeholder="Contoh: 250000"
                        min="{{ (int)$minTopup }}"
                        step="10000"
                        oninput="onCustomAmountInput(this.value)"
                        class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 text-base font-black font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition bg-white"
                    />
                </div>
            </div>

            <!-- Voucher Diskon Input Field -->
            <div class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200/80 space-y-2">
                <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="ticket" class="w-4 h-4 text-emerald-600"></i>
                        <span>Punya Kode Voucher Diskon?</span>
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold">Hemat biaya top-up</span>
                </label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="input_voucher_code"
                        placeholder="Contoh: DISKON50"
                        class="flex-1 px-3.5 py-2 text-xs font-mono font-black uppercase rounded-xl border border-emerald-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none placeholder-slate-400"
                    />
                    <button
                        type="button"
                        onclick="applyVoucher()"
                        id="btn-apply-voucher"
                        class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-1"
                    >
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span id="btn-apply-text">Terapkan</span>
                    </button>
                </div>
                <div id="voucher-status-msg" class="hidden text-xs font-bold pt-1"></div>
            </div>

            <!-- Summary Preview Box -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-5 rounded-2xl space-y-3 shadow-md">
                <div class="flex justify-between items-center text-xs text-slate-300">
                    <span>Total Saldo Poin Yang Diterima:</span>
                    <span id="summary-points" class="font-mono font-black text-emerald-400 text-sm">100.000 Poin</span>
                </div>
                <div id="wrapper-voucher-discount" class="hidden flex justify-between items-center text-xs text-emerald-300 border-t border-slate-700/60 pt-2 font-bold">
                    <span id="label-voucher-name">Diskon Voucher:</span>
                    <span id="summary-discount" class="font-mono text-emerald-300">- Rp 0</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-300 border-t border-slate-700/60 pt-2">
                    <span>Status Level Akun Setelah Pembayaran:</span>
                    <span class="inline-flex items-center gap-1 text-amber-400 font-extrabold">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i> AKUN PRO (PERMANEN)
                    </span>
                </div>
                <div class="flex justify-between items-center text-base font-black text-white border-t border-slate-700/60 pt-3">
                    <span>Total Tagihan Pembayaran:</span>
                    <span id="summary-total" class="font-mono text-amber-400 text-xl">Rp 100.000</span>
                </div>
            </div>

            <!-- Error Message Div -->
            <div id="upgrade-error-msg" class="hidden bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs font-bold"></div>

            <!-- Action Button -->
            <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    <p class="font-semibold text-slate-700">Metode Pembayaran Online Resmi:</p>
                    <p>QRIS, Virtual Account BCA/Mandiri/BNI/BRI, E-Wallet & Kartu Kredit (Pembayaran Instan)</p>
                </div>
                <button
                    type="button"
                    id="btn-checkout-pro"
                    onclick="processTopupCheckout()"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-3.5 rounded-2xl font-black text-sm transition shadow-md hover:shadow-lg active:scale-[0.98] cursor-pointer"
                >
                    <i data-lucide="zap" class="w-4.5 h-4.5 fill-white"></i>
                    <span>Bayar Sekarang</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedAmount = 100000;
    let appliedVoucherCode = '';
    let currentDiscountAmount = 0;
    const minAmount = {{ (int)$minTopup }};

    function selectNominal(amount) {
        selectedAmount = amount;
        document.getElementById('custom_amount').value = '';

        // Reset all preset card styles
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.className = 'preset-btn p-4 rounded-2xl border-2 border-slate-200 bg-white text-left transition hover:border-slate-300 cursor-pointer group';
        });

        // Highlight selected preset
        const selectedBtn = document.getElementById(`preset-${amount}`);
        if (selectedBtn) {
            selectedBtn.className = 'preset-btn p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 text-left transition relative cursor-pointer group';
        }

        updateSummary();
    }

    function onCustomAmountInput(val) {
        const parsed = parseInt(val) || 0;
        selectedAmount = parsed;

        // Reset preset highlights
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.className = 'preset-btn p-4 rounded-2xl border-2 border-slate-200 bg-white text-left transition hover:border-slate-300 cursor-pointer group';
        });

        updateSummary();
    }

    function updateSummary() {
        const errDiv = document.getElementById('upgrade-error-msg');
        errDiv.classList.add('hidden');

        const formatted = new Intl.NumberFormat('id-ID').format(selectedAmount > 0 ? selectedAmount : 0);
        document.getElementById('summary-points').innerText = `${formatted} Poin`;

        const finalAmount = Math.max(0, selectedAmount - currentDiscountAmount);
        const formattedFinal = new Intl.NumberFormat('id-ID').format(finalAmount);
        document.getElementById('summary-total').innerText = `Rp ${formattedFinal}`;

        const wrapperDiscount = document.getElementById('wrapper-voucher-discount');
        if (currentDiscountAmount > 0) {
            wrapperDiscount.classList.remove('hidden');
            document.getElementById('summary-discount').innerText = `- Rp ${new Intl.NumberFormat('id-ID').format(currentDiscountAmount)}`;
        } else {
            wrapperDiscount.classList.add('hidden');
        }
    }

    async function applyVoucher() {
        const codeInput = document.getElementById('input_voucher_code');
        const code = codeInput.value.trim().toUpperCase();
        const statusMsg = document.getElementById('voucher-status-msg');
        const btn = document.getElementById('btn-apply-voucher');

        if (!code) {
            statusMsg.innerText = 'Silakan masukkan kode voucher terlebih dahulu.';
            statusMsg.className = 'text-xs font-bold pt-1 text-rose-600 block';
            return;
        }

        btn.disabled = true;
        document.getElementById('btn-apply-text').innerText = 'Mengecek...';

        try {
            const res = await fetch('/api/vouchers/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    code: code,
                    amount: selectedAmount
                })
            });

            const data = await res.json();

            if (data.success) {
                appliedVoucherCode = data.code;
                currentDiscountAmount = data.discount_amount;
                statusMsg.innerText = `✓ ${data.message}`;
                statusMsg.className = 'text-xs font-bold pt-1 text-emerald-600 block';
                document.getElementById('label-voucher-name').innerText = `Diskon Voucher (${data.code}):`;
                updateSummary();
            } else {
                appliedVoucherCode = '';
                currentDiscountAmount = 0;
                statusMsg.innerText = `✕ ${data.message || 'Voucher tidak valid.'}`;
                statusMsg.className = 'text-xs font-bold pt-1 text-rose-600 block';
                updateSummary();
            }
        } catch (e) {
            statusMsg.innerText = 'Gagal memverifikasi voucher: ' + e.message;
            statusMsg.className = 'text-xs font-bold pt-1 text-rose-600 block';
        } finally {
            btn.disabled = false;
            document.getElementById('btn-apply-text').innerText = 'Terapkan';
        }
    }

    async function processTopupCheckout() {
        const btn = document.getElementById('btn-checkout-pro');
        const errDiv = document.getElementById('upgrade-error-msg');

        errDiv.classList.add('hidden');

        if (selectedAmount < minAmount) {
            errDiv.innerText = `Nominal top-up minimal adalah Rp ${new Intl.NumberFormat('id-ID').format(minAmount)}.`;
            errDiv.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> <span>Menyiapkan Tagihan Pembayaran...</span>';

        try {
            const response = await fetch('/payment/xenith/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    amount: selectedAmount,
                    voucher_code: appliedVoucherCode
                })
            });

            const data = await response.json();

            if (data.success && data.is_free) {
                btn.className = 'w-full py-3.5 bg-emerald-600 text-white font-black rounded-xl text-sm shadow-md flex items-center justify-center gap-2';
                btn.innerHTML = '<i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i> <span>Voucher Berhasil! Mengalihkan...</span>';
                if (window.lucide) lucide.createIcons();
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/admin/dashboard';
                }, 500);
            } else if (data.success && data.payment_url) {
                window.location.href = data.payment_url;
            } else {
                errDiv.innerText = data.error || 'Gagal menyiapkan tagihan pembayaran.';
                errDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="zap" class="w-4.5 h-4.5 fill-white"></i> <span>Bayar Sekarang</span>';
                if (window.lucide) lucide.createIcons();
            }
        } catch (err) {
            errDiv.innerText = 'Terjadi kesalahan koneksi jaringan: ' + err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="zap" class="w-4.5 h-4.5 fill-white"></i> <span>Bayar Sekarang</span>';
            lucide.createIcons();
        }
    }
</script>
@endsection
