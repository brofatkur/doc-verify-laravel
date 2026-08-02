@extends('layouts.app')

@section('title', 'DocVerify IPPTI - Upgrade Akun Mode PRO')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 py-4">
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
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Status Paket & Upgrade PRO</h1>
            <p class="text-slate-500 text-sm">Aktifkan paket Pro sekali bayar untuk menikmati fitur penuh verifikasi dokumen IPPTI.</p>
        </div>
    </div>

    @if(Auth::user()->isPro())
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-40 h-40 bg-amber-500/20 rounded-full blur-3xl"></div>
            <div class="relative z-10 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-amber-500 text-slate-950 rounded-2xl font-extrabold shadow-md">
                        <i data-lucide="award" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-amber-400 tracking-wider uppercase">Status Akun Terverifikasi</span>
                        <h2 class="text-2xl font-black text-white">Akun Anda Adalah AKUN PRO</h2>
                    </div>
                </div>

                <p class="text-slate-300 text-sm leading-relaxed">
                    Selamat! Akun Penerjemah Anda telah berstatus <strong class="text-amber-400">PRO</strong> secara permanen. Anda dapat mendaftarkan dokumen terjemahan tersumpah dan mengelola verifikasi publik tanpa batasan.
                </p>

                <div class="pt-4 border-t border-slate-700/60 flex flex-col sm:flex-row items-center gap-3">
                    <a href="/admin/transactions" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-950 px-6 py-3 rounded-xl font-black text-xs transition shadow-md">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>Lihat Riwayat Pembelian & Poin</span>
                    </a>
                    <a href="/admin" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white px-6 py-3 rounded-xl font-bold text-xs transition border border-slate-700">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Kembali ke Dashboard Dokumen</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 md:p-8 space-y-6">
                <!-- Header Package Pricing Card -->
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-6 rounded-2xl relative overflow-hidden shadow-lg">
                    <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 mb-2">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                                PAKET PRO UNLIMITED
                            </span>
                            <h2 class="text-2xl font-black tracking-tight text-white">Aktivasi Mode PRO</h2>
                            <p class="text-xs text-slate-300 mt-1">Sekali bayar untuk aktivasi permanen akun penerjemah tersumpah.</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Harga Sekali Bayar</p>
                            <p class="text-3xl font-black text-amber-400 font-mono">
                                Rp {{ number_format($proPrice, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features List -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Keuntungan & Fitur Mode PRO:</h3>
                    
                    <div class="space-y-2.5">
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <div class="p-1.5 bg-amber-100 text-amber-700 rounded-lg flex-shrink-0 mt-0.5">
                                <i data-lucide="coins" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Bonus Poin Aktivasi Penuh</p>
                                <p class="text-xs text-slate-600">Langsung mendapatkan saldo bonus <strong class="font-mono text-emerald-700">+{{ number_format($proPoints, 0, ',', '.') }} Poin</strong> setelah aktivasi sukses.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <div class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg flex-shrink-0 mt-0.5">
                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Verifikasi Dokumen & Kode QR Publik</p>
                                <p class="text-xs text-slate-600">Akses penuh pendaftaran dokumen manual & impor massal Excel tanpa batasan trial.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <div class="p-1.5 bg-blue-100 text-blue-700 rounded-lg flex-shrink-0 mt-0.5">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Lencana Akun PRO Resmi IPPTI</p>
                                <p class="text-xs text-slate-600">Status PRO ditampilkan pada profil penerjemah dan pencarian verifikasi publik.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message Div -->
                <div id="upgrade-error-msg" class="hidden bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs font-bold"></div>

                <!-- Action Button -->
                <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        <p class="font-semibold text-slate-700">Metode Pembayaran Online:</p>
                        <p>QRIS, VA BCA/Mandiri/BNI/BRI, E-Wallet & CC (via iPaymu)</p>
                    </div>
                    <button
                        type="button"
                        id="btn-checkout-pro"
                        onclick="processProCheckout()"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-8 py-3.5 rounded-2xl font-black text-sm transition shadow-md hover:shadow-lg active:scale-[0.98] cursor-pointer"
                    >
                        <i data-lucide="zap" class="w-4 h-4 fill-white"></i>
                        <span>Bayar & Aktivasi Mode PRO</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            async function processProCheckout() {
                const btn = document.getElementById('btn-checkout-pro');
                const errDiv = document.getElementById('upgrade-error-msg');

                errDiv.classList.add('hidden');
                btn.disabled = true;
                btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> <span>Menyiapkan Checkout PRO...</span>';

                try {
                    const response = await fetch('/payment/pro-upgrade/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success && data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        errDiv.innerText = data.error || 'Gagal menyiapkan tagihan checkout PRO.';
                        errDiv.classList.remove('hidden');
                        btn.disabled = false;
                        btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-white"></i> <span>Bayar & Aktivasi Mode PRO</span>';
                        lucide.createIcons();
                    }
                } catch (err) {
                    errDiv.innerText = 'Terjadi kesalahan koneksi jaringan: ' + err.message;
                    errDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-white"></i> <span>Bayar & Aktivasi Mode PRO</span>';
                    lucide.createIcons();
                }
            }
        </script>
    @endif
</div>
@endsection
