<div class="flex items-center justify-end gap-2" id="qr-container-{{ $doc->id }}">
    @if($doc->is_qr_generated)
        <button
            onclick="showQrModal('{{ $doc->document_id }}')"
            class="text-slate-600 p-2 hover:bg-slate-100 rounded-lg transition cursor-pointer"
            title="Lihat & Unduh Kode QR"
        >
            <i data-lucide="qr-code" class="w-4 h-4"></i>
        </button>
        <a
            href="/verify/{{ $doc->document_id }}"
            target="_blank"
            class="text-blue-600 p-2 hover:bg-blue-50 rounded-lg transition cursor-pointer"
            title="Buka Halaman Publik"
        >
            <i data-lucide="eye" class="w-4 h-4"></i>
        </a>
    @endif

    @if(Auth::check() && ($doc->translator_id === Auth::id() || Auth::user()->role === 'SUPERADMIN'))
        <a
            href="/admin/documents/{{ $doc->id }}/edit"
            class="text-slate-600 p-2 hover:bg-slate-100 rounded-lg transition cursor-pointer"
            title="Edit Dokumen"
        >
            <i data-lucide="edit-3" class="w-4 h-4"></i>
        </a>
    @endif

    @if(Auth::check() && ($doc->translator_id === Auth::id() || Auth::user()->role === 'SUPERADMIN'))
        <button
            onclick="toggleQrStatus('{{ $doc->id }}', {{ $doc->is_qr_generated ? 'true' : 'false' }})"
            id="btn-toggle-qr-{{ $doc->id }}"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer {{ $doc->is_qr_generated
                    ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200'
                    : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'
                }}"
        >
            <span>{{ $doc->is_qr_generated ? 'Cabut QR' : 'Buat QR' }}</span>
        </button>
    @endif
</div>

<!-- Render QR modal code once globally on layout, or locally here -->
@once
    <!-- QRCode CDN library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>

    <div id="modal-qr-preview" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl p-6 border border-slate-100 text-center relative">
            <button
                onclick="closeQrModal()"
                class="absolute top-4 right-4 p-1 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <h3 class="text-lg font-bold text-slate-900 mb-1">Kode QR Verifikasi</h3>
            <p class="text-xs text-slate-500 mb-4 truncate font-mono">ID: <span id="modal-qr-doc-id"></span></p>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex justify-center items-center mb-4">
                <canvas id="qr-canvas" class="w-48 h-48 rounded"></canvas>
            </div>

            <p id="modal-qr-url" class="text-xs text-slate-400 mb-6 truncate max-w-full font-mono bg-slate-50 p-2 rounded border border-slate-100">
            </p>

            <button
                onclick="downloadQrCode()"
                class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-semibold shadow-sm transition cursor-pointer"
            >
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Unduh PNG</span>
            </button>
        </div>
    </div>

    <script>
        let currentQrDocId = '';
        let currentQrDataUrl = '';

        async function showQrModal(docId) {
            currentQrDocId = docId;
            const origin = window.location.origin;
            const verificationUrl = origin + '/verify/' + docId;
            
            document.getElementById('modal-qr-doc-id').innerText = docId;
            document.getElementById('modal-qr-url').innerText = verificationUrl;

            const canvas = document.getElementById('qr-canvas');
            
            try {
                await QRCode.toCanvas(canvas, verificationUrl, {
                    width: 250,
                    margin: 2,
                    color: {
                        dark: '#0f172a',
                        light: '#ffffff'
                    }
                });
                
                // Convert to dataUrl for downloading
                currentQrDataUrl = canvas.toDataURL("image/png");
                document.getElementById('modal-qr-preview').classList.remove('hidden');
                lucide.createIcons();
            } catch (err) {
                alert('Gagal membuat Kode QR: ' + err.message);
            }
        }

        function closeQrModal() {
            document.getElementById('modal-qr-preview').classList.add('hidden');
        }

        function downloadQrCode() {
            if (!currentQrDataUrl) return;
            const link = document.createElement('a');
            link.href = currentQrDataUrl;
            link.download = 'QR_Verifikasi_' + currentQrDocId + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async function toggleQrStatus(id, currentStatus) {
            const btn = document.getElementById('btn-toggle-qr-' + id);
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';

            try {
                const response = await fetch('/admin/documents/' + id + '/toggle-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    window.location.reload();
                } else {
                    alert('Gagal memperbarui status: ' + (res.error || 'Terjadi kesalahan.'));
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                }
            } catch (err) {
                alert('Kesalahan jaringan: ' + err.message);
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }
        }
    </script>
@endonce
