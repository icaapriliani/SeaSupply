@extends('layouts.buyer')

@section('title', $product['name'])

@section('content')
<div class="ss-page">

    {{-- ── BREADCRUMB ───────────────────────────────────── --}}
    <nav aria-label="Breadcrumb" style="margin-bottom:var(--sp-4);">
        <ol style="display:flex;align-items:center;gap:8px;list-style:none;font-size:var(--fs-label);color:var(--color-muted);">
            <li><a href="{{ route('catalog.index') }}" style="color:var(--color-sea-teal);font-weight:500;">Produk</a></li>
            <li aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </li>
            <li style="color:var(--color-muted);">{{ $product['category'] }}</li>
            <li aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </li>
            <li aria-current="page" style="color:var(--color-ink);font-weight:500;
                    max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $product['name'] }}
            </li>
        </ol>
    </nav>

    {{-- ── PRODUCT DETAIL CARD ──────────────────────────── --}}
    <div class="ss-surface-card" style="padding:0;overflow:hidden;max-width:900px;margin:0 auto;">
        <div class="ss-detail-grid">

            {{-- Left — Product Image --}}
            <div style="
                background: {{ $product['gradient'] }};
                display:flex;align-items:center;justify-content:center;
                padding:var(--sp-6);
                position:relative;
                min-height:340px;
            " aria-label="Foto produk {{ $product['name'] }}">
                <span style="font-size:100px;filter:drop-shadow(0 8px 24px rgba(0,0,0,.12));"
                      aria-hidden="true">{{ $product['emoji'] }}</span>

                {{-- Stock overlay if out of stock --}}
                @if(!$product['is_available'])
                <div style="
                    position:absolute;top:var(--sp-3);left:var(--sp-3);
                " aria-label="Produk tidak tersedia">
                    <span class="ss-badge ss-badge--out-of-stock">Stok Habis</span>
                </div>
                @else
                <div style="position:absolute;top:var(--sp-3);left:var(--sp-3);">
                    <span class="ss-badge ss-badge--available">Tersedia</span>
                </div>
                @endif

                {{-- Category badge --}}
                <div style="position:absolute;bottom:var(--sp-3);left:var(--sp-3);">
                    <span style="
                        background:rgba(255,255,255,.85);
                        backdrop-filter:blur(4px);
                        padding:4px 10px;
                        border-radius:var(--radius-full);
                        font-size:var(--fs-caption);
                        font-weight:var(--fw-semibold);
                        color:var(--color-ink);
                    ">{{ $product['emoji'] }} {{ $product['category'] }}</span>
                </div>
            </div>

            {{-- Right — Product Info & Order Form --}}
            <div style="padding:var(--sp-6);display:flex;flex-direction:column;gap:var(--sp-4);">

                {{-- Product Name & Store --}}
                <div>
                    <h1 class="ss-h2" style="margin-bottom:var(--sp-1);">{{ $product['name'] }}</h1>
                    <div class="ss-card__store" style="font-size:var(--fs-label);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        </svg>
                        <span style="font-weight:500;color:var(--color-ink);">{{ $product['store_name'] }}</span>
                        <span style="color:var(--color-muted);">&bull; {{ $product['store_location'] }}</span>
                    </div>
                </div>

                {{-- Price --}}
                <div>
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:2px;">Harga</div>
                    <div style="font-size:28px;font-weight:var(--fw-bold);color:var(--color-ocean-navy);">
                        Rp {{ number_format($product['price'], 0, ',', '.') }}
                        <span style="font-size:var(--fs-body);font-weight:var(--fw-regular);color:var(--color-muted);">/ {{ $product['unit'] }}</span>
                    </div>
                </div>

                {{-- Stock Status --}}
                <div style="display:flex;align-items:center;gap:var(--sp-3);">
                    <div>
                        <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:2px;">Ketersediaan stok</div>
                        @if(!$product['is_available'])
                            <span class="ss-badge ss-badge--out-of-stock">Stok Habis</span>
                        @elseif($product['stock'] <= 10)
                            <span class="ss-badge ss-badge--pending">
                                Sisa {{ $product['stock'] }} {{ $product['unit'] }}
                            </span>
                        @else
                            <span class="ss-badge ss-badge--available">
                                {{ $product['stock'] }} {{ $product['unit'] }} tersedia
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Quantity Selector --}}
                @if($product['is_available'])
                <div>
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:var(--sp-2);">Jumlah pesanan</div>
                    <div class="ss-qty" id="qtyControl" aria-label="Pilih jumlah pesanan">
                        <button class="ss-qty__btn"
                                id="qtyDecrease"
                                aria-label="Kurangi jumlah"
                                data-action="decrease">−</button>
                        <div class="ss-qty__value"
                             id="qtyDisplay"
                             role="spinbutton"
                             aria-valuenow="1"
                             aria-valuemin="1"
                             aria-valuemax="{{ $product['stock'] }}">1</div>
                        <button class="ss-qty__btn"
                                id="qtyIncrease"
                                aria-label="Tambah jumlah"
                                data-action="increase">+</button>
                    </div>
                    <p style="font-size:var(--fs-caption);color:var(--color-muted);margin-top:var(--sp-1);">
                        Maks. {{ $product['stock'] }} {{ $product['unit'] }}
                    </p>
                </div>

                {{-- Subtotal Preview --}}
                <div style="
                    background:var(--color-seafoam-bg);
                    border:1px solid var(--color-seafoam);
                    border-radius:var(--radius-sm);
                    padding:var(--sp-3) var(--sp-4);
                    display:flex;justify-content:space-between;align-items:center;
                " aria-live="polite" aria-label="Estimasi total harga">
                    <span style="font-size:var(--fs-label);color:var(--color-muted);">Estimasi subtotal</span>
                    <span id="subtotalDisplay"
                          style="font-size:16px;font-weight:var(--fw-bold);color:var(--color-ocean-navy);">
                        Rp {{ number_format($product['price'], 0, ',', '.') }}
                    </span>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div style="display:flex;gap:var(--sp-3);margin-top:auto;flex-wrap:wrap;">
                    @if($product['is_available'])
                    <button class="ss-btn ss-btn--outline"
                            id="btnAddShowToCart"
                            style="flex:1;min-width:150px;"
                            aria-label="Tambah {{ $product['name'] }} ke keranjang">
                        🛒 + Keranjang
                    </button>
                    <button class="ss-btn ss-btn--primary"
                            id="btnBuatPesanan"
                            style="flex:1;min-width:150px;"
                            aria-label="Buat pesanan untuk {{ $product['name'] }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Beli langsung
                    </button>
                    @else
                    <button class="ss-btn ss-btn--disabled" style="flex:1;" disabled aria-disabled="true">
                        Stok Habis
                    </button>
                    @endif

                    <a href="{{ route('catalog.index') }}"
                       class="ss-btn ss-btn--outline"
                       aria-label="Kembali ke katalog produk">
                        Kembali
                    </a>
                </div>

            </div>
        </div>

        {{-- ── DESCRIPTION SECTION ─────────────────────── --}}
        <div style="
            padding:var(--sp-5) var(--sp-6);
            border-top:1px solid var(--color-border);
        ">
            <h2 style="font-size:var(--fs-h3);font-weight:var(--fw-semibold);color:var(--color-ink);margin-bottom:var(--sp-3);">
                Deskripsi produk
            </h2>
            <p style="font-size:var(--fs-body);color:var(--color-muted);line-height:1.75;max-width:600px;">
                {{ $product['description'] ?? 'Informasi produk belum tersedia.' }}
            </p>

            {{-- Additional Info Grid --}}
            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
                gap:var(--sp-3);
                margin-top:var(--sp-5);
            ">
                <div style="
                    padding:var(--sp-3);
                    background:var(--color-surface);
                    border-radius:var(--radius-sm);
                    border:1px solid var(--color-border);
                ">
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:4px;">Kategori</div>
                    <div style="font-weight:var(--fw-semibold);color:var(--color-ink);">{{ $product['category'] }}</div>
                </div>
                <div style="
                    padding:var(--sp-3);
                    background:var(--color-surface);
                    border-radius:var(--radius-sm);
                    border:1px solid var(--color-border);
                ">
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:4px;">Satuan</div>
                    <div style="font-weight:var(--fw-semibold);color:var(--color-ink);">per {{ $product['unit'] }}</div>
                </div>
                <div style="
                    padding:var(--sp-3);
                    background:var(--color-surface);
                    border-radius:var(--radius-sm);
                    border:1px solid var(--color-border);
                ">
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:4px;">Penjual</div>
                    <div style="font-weight:var(--fw-semibold);color:var(--color-ink);">{{ $product['store_name'] }}</div>
                </div>
                <div style="
                    padding:var(--sp-3);
                    background:var(--color-surface);
                    border-radius:var(--radius-sm);
                    border:1px solid var(--color-border);
                ">
                    <div style="font-size:var(--fs-caption);color:var(--color-muted);margin-bottom:4px;">Lokasi</div>
                    <div style="font-weight:var(--fw-semibold);color:var(--color-ink);">{{ $product['store_location'] }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── MODAL KONFIRMASI PESANAN (WIREFRAME B FORM CHECKOUT) ── --}}
<div class="ss-modal-overlay" id="modalKonfirmasi" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="ss-modal" style="max-width:480px;max-height:90vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="modalTitle">
            <span aria-hidden="true">📋</span> Konfirmasi Pesanan
        </h2>
        <div class="ss-modal__body" style="margin-bottom:var(--sp-4);">

            {{-- Ringkasan Pesanan --}}
            <div style="
                background:var(--color-surface);
                border:1px solid var(--color-border);
                border-radius:var(--radius-sm);
                padding:var(--sp-3) var(--sp-4);
                margin-bottom:var(--sp-4);
            ">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:var(--fs-label);color:var(--color-muted);">Produk</span>
                    <span style="font-size:var(--fs-label);font-weight:var(--fw-semibold);color:var(--color-ink);">{{ $product['name'] }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-size:var(--fs-label);color:var(--color-muted);">Jumlah</span>
                    <span style="font-size:var(--fs-label);font-weight:var(--fw-semibold);color:var(--color-ink);" id="modalQty">1 {{ $product['unit'] }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid var(--color-border);margin-top:4px;">
                    <span style="font-size:var(--fs-label);font-weight:var(--fw-semibold);">Total Pembayaran</span>
                    <span style="font-size:16px;font-weight:var(--fw-bold);color:var(--color-ocean-navy);" id="modalTotal">
                        Rp {{ number_format($product['price'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Form Informasi Pengiriman --}}
            <div style="display:flex;flex-direction:column;gap:var(--sp-3);">
                <div style="font-size:var(--fs-label);font-weight:var(--fw-semibold);color:var(--color-ocean-navy);border-bottom:1px solid var(--color-border);padding-bottom:4px;">
                    📍 Informasi Penerima & Pengiriman
                </div>

                {{-- Alert Error Client Side --}}
                <div id="checkoutErrorBox" class="ss-alert ss-alert--danger" style="display:none;padding:8px 12px;font-size:12px;margin-bottom:0;" role="alert">
                    <span id="checkoutErrorMessage">Mohon lengkapi data pengiriman.</span>
                </div>

                {{-- Nama Penerima --}}
                <div class="ss-input-group">
                    <label for="checkoutRecipientName" class="ss-label">
                        Nama Penerima <span style="color:var(--color-danger);">&ast;</span>
                    </label>
                    <input
                        type="text"
                        id="checkoutRecipientName"
                        class="ss-input"
                        placeholder="Nama lengkap penerima"
                        value="{{ Auth::user()?->name ?? '' }}"
                        required
                    >
                </div>

                {{-- Nomor WhatsApp / Telepon --}}
                <div class="ss-input-group">
                    <label for="checkoutRecipientPhone" class="ss-label">
                        Nomor WhatsApp / Telepon <span style="color:var(--color-danger);">&ast;</span>
                    </label>
                    <input
                        type="tel"
                        id="checkoutRecipientPhone"
                        class="ss-input"
                        placeholder="Contoh: 0812-3456-7890"
                        value="{{ Auth::user()?->phone ?? '' }}"
                        required
                    >
                </div>

                {{-- Alamat Pengiriman Lengkap --}}
                <div class="ss-input-group">
                    <label for="checkoutDeliveryAddress" class="ss-label">
                        Alamat Pengiriman Lengkap <span style="color:var(--color-danger);">&ast;</span>
                    </label>
                    <textarea
                        id="checkoutDeliveryAddress"
                        class="ss-input"
                        rows="2"
                        style="height:auto;padding-top:8px;padding-bottom:8px;resize:vertical;"
                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota..."
                        required
                    ></textarea>
                </div>

                {{-- Catatan Pesanan --}}
                <div class="ss-input-group">
                    <label for="checkoutBuyerNote" class="ss-label">
                        Catatan Pesanan <span style="color:var(--color-muted);font-weight:400;">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        id="checkoutBuyerNote"
                        class="ss-input"
                        placeholder='misal: "minta es batu / packing kardus"'
                    >
                </div>
            </div>

        </div>
        <div class="ss-modal__actions" style="margin-top:var(--sp-4);">
            <button type="button" class="ss-btn ss-btn--outline" id="btnModalBatal" aria-label="Batalkan dan kembali">
                Kembali
            </button>
            <button type="button" class="ss-btn ss-btn--primary" id="btnModalLanjut" aria-label="Konfirmasi dan buat pesanan">
                Ya, lanjutkan
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const price    = {{ $product['price'] }};
    const maxStock = {{ $product['stock'] }};
    const unit     = '{{ $product['unit'] }}';

    let qty = 1;

    const qtyDisplay   = document.getElementById('qtyDisplay');
    const qtyDecrease  = document.getElementById('qtyDecrease');
    const qtyIncrease  = document.getElementById('qtyIncrease');
    const subtotal     = document.getElementById('subtotalDisplay');
    const modalQty     = document.getElementById('modalQty');
    const modalTotal   = document.getElementById('modalTotal');

    function formatRp(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    function updateUI() {
        qtyDisplay.textContent = qty;
        qtyDisplay.setAttribute('aria-valuenow', qty);

        if (subtotal)   subtotal.textContent = formatRp(price * qty);
        if (modalQty)   modalQty.textContent  = qty + ' ' + unit;
        if (modalTotal) modalTotal.textContent = formatRp(price * qty);

        if (qtyDecrease) qtyDecrease.disabled = qty <= 1;
        if (qtyIncrease) qtyIncrease.disabled = qty >= maxStock;
    }

    if (qtyDecrease) {
        qtyDecrease.addEventListener('click', function () {
            if (qty > 1) { qty--; updateUI(); }
        });
    }

    if (qtyIncrease) {
        qtyIncrease.addEventListener('click', function () {
            if (qty < maxStock) { qty++; updateUI(); }
        });
    }

    // Modal & Checkout Inputs
    const modal            = document.getElementById('modalKonfirmasi');
    const btnBuat          = document.getElementById('btnBuatPesanan');
    const btnBatal         = document.getElementById('btnModalBatal');
    const btnLanjut        = document.getElementById('btnModalLanjut');
    const recipientNameInp = document.getElementById('checkoutRecipientName');
    const recipientPhoneInp= document.getElementById('checkoutRecipientPhone');
    const deliveryAddrInp  = document.getElementById('checkoutDeliveryAddress');
    const buyerNoteInp     = document.getElementById('checkoutBuyerNote');
    const errorBox         = document.getElementById('checkoutErrorBox');
    const errorMessage     = document.getElementById('checkoutErrorMessage');

    function openModal() {
        updateUI();
        if (errorBox) errorBox.style.display = 'none';
        modal.classList.add('open');
        if (recipientNameInp && !recipientNameInp.value) {
            recipientNameInp.focus();
        } else if (deliveryAddrInp && !deliveryAddrInp.value) {
            deliveryAddrInp.focus();
        }
    }

    function closeModal() {
        modal.classList.remove('open');
        if (btnBuat) btnBuat.focus();
    }

    if (btnBuat)   btnBuat.addEventListener('click', openModal);
    if (btnBatal)  btnBatal.addEventListener('click', closeModal);

    // Close on overlay click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
    });

    if (btnLanjut) {
        btnLanjut.addEventListener('click', function () {
            const recipientName   = recipientNameInp ? recipientNameInp.value.trim() : '';
            const recipientPhone  = recipientPhoneInp ? recipientPhoneInp.value.trim() : '';
            const deliveryAddress = deliveryAddrInp ? deliveryAddrInp.value.trim() : '';
            const buyerNote       = buyerNoteInp ? buyerNoteInp.value.trim() : '';

            // Client side validation
            if (!recipientName) {
                showError('Mohon isi nama penerima pesanan.');
                recipientNameInp.focus();
                return;
            }

            if (!recipientPhone) {
                showError('Mohon isi nomor WhatsApp/telepon penerima.');
                recipientPhoneInp.focus();
                return;
            }

            if (!deliveryAddress) {
                showError('Mohon isi alamat pengiriman lengkap.');
                deliveryAddrInp.focus();
                return;
            }

            if (errorBox) errorBox.style.display = 'none';

            btnLanjut.disabled = true;
            btnLanjut.textContent = 'Memproses...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('{{ route('catalog.order.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    product_id: {{ $product['id'] }},
                    quantity: qty,
                    recipient_name: recipientName,
                    recipient_phone: recipientPhone,
                    delivery_address: deliveryAddress,
                    buyer_note: buyerNote
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                btnLanjut.disabled = false;
                btnLanjut.textContent = 'Ya, lanjutkan';

                if (status === 201) {
                    closeModal();

                    const toast = document.createElement('div');
                    toast.className = 'ss-toast ss-toast--success';
                    toast.setAttribute('role', 'alert');
                    toast.innerHTML = '✓ Pesanan #' + body.data.order_code + ' berhasil dibuat! Sisa stok: ' + body.data.remaining_stock + ' ' + unit;
                    document.getElementById('toastContainer').appendChild(toast);
                    setTimeout(() => toast.remove(), 4500);

                    // Arahkan ke halaman riwayat pesanan setelah 1.5 detik
                    setTimeout(() => {
                        window.location.href = '{{ route('catalog.orders') }}';
                    }, 1500);
                } else {
                    const errMsg = body.error?.message || (body.message ?? 'Gagal memproses pesanan.');
                    showError(errMsg);

                    const toast = document.createElement('div');
                    toast.className = 'ss-toast ss-toast--danger';
                    toast.setAttribute('role', 'alert');
                    toast.innerHTML = '⚠️ ' + errMsg;
                    document.getElementById('toastContainer').appendChild(toast);
                    setTimeout(() => toast.remove(), 4500);
                }
            })
            .catch(err => {
                btnLanjut.disabled = false;
                btnLanjut.textContent = 'Ya, lanjutkan';
                showError('Terjadi kesalahan jaringan atau server.');
            });
        });
    }

    function showError(msg) {
        if (errorBox && errorMessage) {
            errorMessage.textContent = msg;
            errorBox.style.display = 'flex';
        } else {
            alert(msg);
        }
    }

    const btnAddCart = document.getElementById('btnAddShowToCart');
    if (btnAddCart) {
        btnAddCart.addEventListener('click', function () {
            if (!window.SeaSupplyCart) return;
            window.SeaSupplyCart.addItem(@json($product), qty);
            const toast = document.createElement('div');
            toast.className = 'ss-toast ss-toast--success';
            toast.innerHTML = `✓ ${qty} ${unit} <strong>{{ $product['name'] }}</strong> masuk ke keranjang.`;
            document.getElementById('toastContainer').appendChild(toast);
            setTimeout(() => toast.remove(), 3200);
        });
    }

    // Init
    updateUI();
})();
</script>
@endpush
