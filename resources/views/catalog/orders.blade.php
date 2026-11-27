@extends('layouts.buyer')

@section('title', 'Pesanan Saya')

@section('content')
<div class="ss-page">

    {{-- ── BREADCRUMB ───────────────────────────────────── --}}
    <nav aria-label="Breadcrumb" style="margin-bottom:var(--sp-4);">
        <ol style="display:flex;align-items:center;gap:8px;list-style:none;font-size:var(--fs-label);color:var(--color-muted);">
            <li><a href="{{ route('catalog.index') }}" style="color:var(--color-sea-teal);font-weight:500;">Beranda</a></li>
            <li aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </li>
            <li aria-current="page" style="color:var(--color-ink);font-weight:600;">Pesanan Saya</li>
        </ol>
    </nav>

    {{-- ── SECTION HEADER & FILTER ──────────────────────── --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
        <div>
            <h1 class="ss-h2" style="margin-bottom:4px;">Riwayat Pesanan Anda</h1>
            <p class="ss-caption">Pantau status pemrosesan dan pengiriman produk olahan laut yang Anda pesan.</p>
        </div>

        {{-- Filter Tab Status --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="{{ route('catalog.orders') }}" 
               class="ss-chip {{ !request('status') || request('status') === 'all' ? 'active' : '' }}" style="padding:5px 14px;">
                Semua ({{ count($allOrders) }})
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'pending']) }}" 
               class="ss-chip {{ request('status') === 'pending' ? 'active' : '' }}" style="padding:5px 14px;">
                Menunggu Pembayaran
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'processing']) }}" 
               class="ss-chip {{ request('status') === 'processing' ? 'active' : '' }}" style="padding:5px 14px;">
                Diproses
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'shipped']) }}" 
               class="ss-chip {{ request('status') === 'shipped' ? 'active' : '' }}" style="padding:5px 14px;">
                Dikirim
            </a>
            <a href="{{ route('catalog.orders', ['status' => 'completed']) }}" 
               class="ss-chip {{ request('status') === 'completed' ? 'active' : '' }}" style="padding:5px 14px;">
                Selesai
            </a>
        </div>
    </div>

    @if(count($orders) === 0)
    {{-- ── EMPTY STATE ──────────────────────────────────── --}}
    <div class="ss-surface-card" style="text-align:center;padding:var(--sp-8) var(--sp-4);">
        <div style="font-size:48px;margin-bottom:var(--sp-3);" aria-hidden="true">🛒</div>
        <h2 class="ss-h3" style="margin-bottom:var(--sp-1);">Belum ada pesanan</h2>
        <p class="ss-caption" style="max-width:320px;margin:0 auto var(--sp-4);">
            Anda belum memiliki pesanan dengan filter status ini. Silakan jelajahi katalog untuk menemukan produk olahan laut segar.
        </p>
        <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--primary">
            Jelajahi Katalog Sekarang
        </a>
    </div>

    @else
    {{-- ── LIST PESANAN (Kombinasi Cards & Table sesuai Design System) ── --}}
    <div style="display:flex;flex-direction:column;gap:var(--sp-4);">
        @foreach($orders as $order)
        <article class="ss-surface-card" style="padding:var(--sp-4);transition:box-shadow .15s;" 
                 onmouseover="this.style.boxShadow='var(--shadow-medium)'" 
                 onmouseout="this.style.boxShadow='var(--shadow-low)'">
            
            {{-- Header Kartu Pesanan --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:var(--sp-3);border-bottom:1px solid var(--color-border);flex-wrap:wrap;gap:var(--sp-2);">
                <div style="display:flex;align-items:center;gap:var(--sp-3);">
                    <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--color-seafoam-bg);color:var(--color-sea-teal);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        📦
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:15px;">
                                #{{ $order['order_code'] }}
                            </strong>
                            <span class="ss-caption">&bull; {{ $order['date'] }}</span>
                        </div>
                        <div class="ss-caption" style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            Penjual: <strong>{{ $order['store_name'] }}</strong> ({{ $order['store_location'] }})
                        </div>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div id="orderStatusWrap-{{ $order['order_code'] }}" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    @if($order['status'] === 'pending')
                        <span class="ss-badge ss-badge--pending">Menunggu Pembayaran</span>
                    @elseif($order['status'] === 'processing')
                        <span class="ss-badge ss-badge--processing">Diproses</span>
                    @elseif($order['status'] === 'shipped')
                        <span class="ss-badge ss-badge--shipped">Dikirim</span>
                    @elseif($order['status'] === 'completed')
                        <span class="ss-badge ss-badge--completed">Selesai</span>
                    @elseif($order['status'] === 'cancelled')
                        <span class="ss-badge ss-badge--cancelled">Dibatalkan</span>
                    @endif
                </div>
            </div>

            {{-- Body: Item Pesanan Snapshot --}}
            <div style="padding:var(--sp-3) 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--sp-3);">
                <div>
                    <div style="font-weight:600;font-size:15px;color:var(--color-ink);">
                        {{ $order['item_name'] }}
                    </div>
                    <div class="ss-caption" style="margin-top:2px;">
                        {{ $order['quantity'] }} {{ $order['unit'] }} &times; Rp {{ number_format($order['unit_price'], 0, ',', '.') }}
                    </div>
                    @if($order['recipient_name'])
                    <div class="ss-caption" style="margin-top:4px;color:var(--color-muted);">
                        Penerima: {{ $order['recipient_name'] }} ({{ $order['recipient_phone'] }})
                    </div>
                    @endif
                </div>

                <div style="text-align:right;">
                    <div class="ss-caption">Total Pembayaran</div>
                    <div style="font-size:18px;font-weight:700;color:var(--color-ocean-navy);">
                        Rp {{ number_format($order['subtotal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Footer Kartu: Bantuan & Detail --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:var(--sp-3);border-top:1px dashed var(--color-border);flex-wrap:wrap;gap:var(--sp-2);">
                <span class="ss-caption" id="orderHint-{{ $order['order_code'] }}" style="color:var(--color-muted);">
                    @if($order['status'] === 'pending')
                        ⏳ Segera lakukan transfer dan unggah bukti pembayaran Anda.
                    @elseif($order['status'] === 'processing')
                        📦 Toko sedang menyiapkan dan mengemas produk olahan laut Anda.
                    @elseif($order['status'] === 'shipped')
                        🚚 Produk dalam perjalanan menuju alamat Anda.
                    @elseif($order['status'] === 'completed')
                        ✓ Pesanan telah selesai diterima. Terima kasih!
                    @endif
                </span>

                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    @if($order['status'] === 'pending')
                    <button type="button"
                            class="ss-btn ss-btn--primary ss-btn--sm btn-upload-proof"
                            id="btnProof-{{ $order['order_code'] }}"
                            data-order="{{ json_encode($order) }}">
                        📤 Upload Bukti Transfer
                    </button>
                    @endif
                    <button class="ss-btn ss-btn--outline ss-btn--sm btn-show-order-detail" 
                            data-order="{{ json_encode($order) }}">
                        Rincian Pesanan
                    </button>
                </div>
            </div>

        </article>
        @endforeach
    </div>
    @endif

</div>

{{-- ── MODAL RINCIAN PESANAN ───────────────────────────── --}}
<div class="ss-modal-overlay" id="modalBuyerOrder" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:460px;">
        <h2 class="ss-modal__title" id="buyerModalTitle">Rincian Transaksi</h2>
        
        <div id="buyerModalContent" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-4);margin:var(--sp-3) 0;">
            {{-- Dynamic via JS --}}
        </div>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--primary ss-btn--full" id="btnCloseBuyerModal">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL UPLOAD BUKTI TRANSFER PEMBAYARAN ──────────── --}}
<div class="ss-modal-overlay" id="modalUploadProof" role="dialog" aria-modal="true" aria-labelledby="uploadProofTitle">
    <div class="ss-modal" style="max-width:480px;max-height:92vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="uploadProofTitle">📤 Upload Bukti Transfer</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-3);">
            Unggah foto/screenshot struk transfer pembayaran untuk pesanan <strong id="proofOrderCodeLabel">#SS-000</strong>.
        </p>

        {{-- Info Rekening & Nominal --}}
        <div style="background:var(--color-seafoam-bg);border:1px solid var(--color-seafoam);border-radius:var(--radius-sm);padding:var(--sp-3);margin-bottom:var(--sp-4);font-size:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="color:var(--color-muted);">Total Tagihan</span>
                <strong id="proofTotalAmount" style="font-size:16px;color:var(--color-ocean-navy);">Rp 0</strong>
            </div>
            <div style="padding-top:6px;border-top:1px dashed rgba(18,59,74,0.2);color:var(--color-ocean-navy);line-height:1.5;">
                <div><strong>BCA:</strong> 139-489-2011 (a.n Usaha Pak Hasan)</div>
                <div><strong>BRI:</strong> 0021-01-045892-50-3 (a.n Usaha Pak Hasan)</div>
            </div>
        </div>

        <form id="formUploadProof" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <input type="hidden" id="proofOrderCodeInput">

            <div class="ss-two-col-grid">
                <div class="ss-input-group">
                    <label class="ss-label" for="proofBankOrigin">Bank / E-Wallet Pengirim *</label>
                    <select id="proofBankOrigin" class="ss-select" required>
                        <option value="BCA">Transfer Bank BCA</option>
                        <option value="BRI">Transfer Bank BRI</option>
                        <option value="Mandiri">Transfer Bank Mandiri</option>
                        <option value="BNI">Transfer Bank BNI</option>
                        <option value="QRIS / E-Wallet">QRIS / GoPay / DANA / OVO</option>
                    </select>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="proofSenderName">Nama Pengirim *</label>
                    <input type="text" id="proofSenderName" class="ss-input" value="{{ Auth::user()?->name ?? '' }}" placeholder="a.n pemilik rekening" required>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="proofImageInput">Foto Bukti Transfer (JPG / PNG / WEBP, Maks 2MB) *</label>
                <label class="ss-upload-dropzone" for="proofImageInput" id="proofDropzoneLabel">
                    <div style="font-size:26px;margin-bottom:4px;">📷</div>
                    <div style="font-size:13px;font-weight:600;color:var(--color-ocean-navy);">Pilih atau ketuk untuk unggah foto struk</div>
                    <div class="ss-caption">Pastikan nominal dan tanggal transfer terlihat jelas</div>
                </label>
                <input type="file" id="proofImageInput" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;">

                <div id="proofPreviewWrap" style="display:none;margin-top:8px;position:relative;width:fit-content;">
                    <img id="proofPreviewImg" src="" alt="Pratinjau bukti transfer" style="max-width:100%;max-height:200px;border-radius:var(--radius-sm);border:1.5px solid var(--color-sea-teal);object-fit:contain;">
                    <button type="button" id="btnRemoveProofImg" style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;border:none;background:var(--color-danger);color:#fff;cursor:pointer;font-weight:700;" title="Ganti foto">&times;</button>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="proofNote">Catatan Tambahan (Opsional)</label>
                <input type="text" id="proofNote" class="ss-input" placeholder="Contoh: Transfer via m-BCA jam 14:15">
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnCloseUploadProof">Batal</button>
                <button type="submit" class="ss-btn ss-btn--primary" id="btnSubmitUploadProof">Kirim Bukti Transfer</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL LIHAT BUKTI TRANSFER ───────────────────────── --}}
<div class="ss-modal-overlay" id="modalViewProof" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:440px;max-height:90vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="viewProofTitle">Bukti Transfer Terkirim</h2>
        <div id="viewProofMeta" class="ss-caption" style="margin-bottom:var(--sp-3);line-height:1.5;"></div>
        <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:8px;text-align:center;margin-bottom:var(--sp-4);">
            <img id="viewProofImg" src="" alt="Bukti transfer" style="max-width:100%;max-height:280px;margin:0 auto;border-radius:var(--radius-xs);">
        </div>
        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--primary ss-btn--full" id="btnCloseViewProof">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const PROOF_STORAGE_KEY = 'seasupply_payment_proofs_v1';
    const modal     = document.getElementById('modalBuyerOrder');
    const closeBtn  = document.getElementById('btnCloseBuyerModal');
    const modalBody = document.getElementById('buyerModalContent');

    document.querySelectorAll('.btn-show-order-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.order);

            modalBody.innerHTML = `
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Kode Transaksi</span>
                    <strong style="color:var(--color-ocean-navy);font-family:monospace;font-size:15px;">#${data.order_code}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Tanggal Pesan</span>
                    <span style="font-weight:500;">${data.date}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Toko Penjual</span>
                    <span style="font-weight:600;color:var(--color-sea-teal);">${data.store_name}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <span class="ss-caption">Status Saat Ini</span>
                    <span class="ss-badge ss-badge--${data.status}">${data.status === 'pending' ? 'Menunggu Pembayaran' : data.status}</span>
                </div>
                <div style="margin:10px 0;padding:10px;background:white;border-radius:var(--radius-xs);border:1px solid var(--color-border);">
                    <div style="font-weight:600;margin-bottom:4px;">${data.item_name}</div>
                    <div class="ss-caption">${data.quantity} ${data.unit} &times; Rp ${parseInt(data.unit_price).toLocaleString('id-ID')}</div>
                </div>
                ${data.recipient_name || data.delivery_address ? `
                <div style="margin:10px 0;padding:10px;background:white;border-radius:var(--radius-xs);border:1px solid var(--color-border);font-size:12px;">
                    <div style="font-weight:600;color:var(--color-ocean-navy);margin-bottom:4px;">📍 Alamat & Penerima</div>
                    <div><strong>${data.recipient_name || '-'}</strong> (${data.recipient_phone || '-'})</div>
                    <div style="color:var(--color-muted);margin-top:2px;">${data.delivery_address || 'Alamat tidak dicantumkan'}</div>
                    ${data.buyer_note ? `<div style="margin-top:6px;padding-top:4px;border-top:1px dashed var(--color-border);color:var(--color-sea-teal);"><strong>Catatan:</strong> ${data.buyer_note}</div>` : ''}
                </div>
                ` : ''}
                <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px solid var(--color-border);">
                    <span style="font-weight:700;">Subtotal Akhir</span>
                    <strong style="color:var(--color-ocean-navy);font-size:16px;">Rp ${parseInt(data.subtotal).toLocaleString('id-ID')}</strong>
                </div>
            `;

            modal.classList.add('open');
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    }

    // ── LOGIKA UPLOAD BUKTI TRANSFER ──────────────────────────────────────
    const modalProof = document.getElementById('modalUploadProof');
    const btnCloseProof = document.getElementById('btnCloseUploadProof');
    const formProof = document.getElementById('formUploadProof');
    const inputProofImg = document.getElementById('proofImageInput');
    const previewWrap = document.getElementById('proofPreviewWrap');
    const previewImg = document.getElementById('proofPreviewImg');
    const dropzoneLabel = document.getElementById('proofDropzoneLabel');
    const btnRemoveImg = document.getElementById('btnRemoveProofImg');

    const modalViewProof = document.getElementById('modalViewProof');
    const btnCloseViewProof = document.getElementById('btnCloseViewProof');

    let selectedProofDataUrl = '';

    function readProofs() {
        try {
            return JSON.parse(localStorage.getItem(PROOF_STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function saveProof(code, payload) {
        const all = readProofs();
        all[code] = payload;
        localStorage.setItem(PROOF_STORAGE_KEY, JSON.stringify(all));
    }

    function applyProofStates() {
        const proofs = readProofs();
        Object.keys(proofs).forEach(code => {
            const p = proofs[code];
            const btn = document.getElementById('btnProof-' + code);
            const statusWrap = document.getElementById('orderStatusWrap-' + code);
            const hint = document.getElementById('orderHint-' + code);

            if (statusWrap && !statusWrap.querySelector('.ss-proof-sent-badge')) {
                const badge = document.createElement('span');
                badge.className = 'ss-badge ss-badge--processing ss-proof-sent-badge';
                badge.textContent = 'Bukti Terkirim';
                statusWrap.appendChild(badge);
            }

            if (hint) {
                hint.innerHTML = `✓ Bukti transfer (${p.bank}) dikirim pada ${p.uploadedAt}. Menunggu verifikasi toko.`;
                hint.style.color = 'var(--color-sea-teal)';
            }

            if (btn) {
                btn.classList.remove('ss-btn--primary');
                btn.classList.add('ss-btn--outline');
                btn.textContent = '🖼️ Lihat Bukti';
                btn.dataset.proofMode = 'view';
            }
        });
    }

    document.querySelectorAll('.btn-upload-proof').forEach(btn => {
        btn.addEventListener('click', function () {
            const order = JSON.parse(this.dataset.order);
            const proofs = readProofs();

            if (this.dataset.proofMode === 'view' && proofs[order.order_code]) {
                const p = proofs[order.order_code];
                document.getElementById('viewProofTitle').textContent = 'Bukti Transfer #' + order.order_code;
                document.getElementById('viewProofMeta').innerHTML =
                    `<strong>Pengirim:</strong> ${p.senderName} (${p.bank})<br><strong>Waktu Unggah:</strong> ${p.uploadedAt}` +
                    (p.note ? `<br><strong>Catatan:</strong> ${p.note}` : '');
                document.getElementById('viewProofImg').src = p.imageDataUrl;
                modalViewProof.classList.add('open');
                return;
            }

            document.getElementById('proofOrderCodeInput').value = order.order_code;
            document.getElementById('proofOrderCodeLabel').textContent = '#' + order.order_code;
            document.getElementById('proofTotalAmount').textContent = 'Rp ' + parseInt(order.subtotal, 10).toLocaleString('id-ID');
            resetProofPreview();
            modalProof.classList.add('open');
        });
    });

    inputProofImg.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2MB.');
            resetProofPreview();
            return;
        }
        const reader = new FileReader();
        reader.onload = function (ev) {
            selectedProofDataUrl = ev.target.result;
            previewImg.src = selectedProofDataUrl;
            previewWrap.style.display = 'block';
            dropzoneLabel.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    function resetProofPreview() {
        inputProofImg.value = '';
        selectedProofDataUrl = '';
        previewImg.src = '';
        previewWrap.style.display = 'none';
        dropzoneLabel.style.display = 'block';
    }

    btnRemoveImg.addEventListener('click', resetProofPreview);
    btnCloseProof.addEventListener('click', () => modalProof.classList.remove('open'));
    modalProof.addEventListener('click', (e) => { if (e.target === modalProof) modalProof.classList.remove('open'); });
    btnCloseViewProof.addEventListener('click', () => modalViewProof.classList.remove('open'));
    modalViewProof.addEventListener('click', (e) => { if (e.target === modalViewProof) modalViewProof.classList.remove('open'); });

    formProof.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!selectedProofDataUrl) {
            alert('Silakan pilih foto bukti transfer terlebih dahulu.');
            return;
        }

        const code = document.getElementById('proofOrderCodeInput').value;
        const bank = document.getElementById('proofBankOrigin').value;
        const senderName = document.getElementById('proofSenderName').value.trim();
        const note = document.getElementById('proofNote').value.trim();

        saveProof(code, {
            bank: bank,
            senderName: senderName,
            note: note,
            imageDataUrl: selectedProofDataUrl,
            uploadedAt: new Date().toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        });

        modalProof.classList.remove('open');
        formProof.reset();
        resetProofPreview();
        applyProofStates();

        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--success';
        toast.textContent = `✓ Bukti transfer untuk #${code} berhasil dikirim!`;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    });

    applyProofStates();
});
</script>
@endpush
