@extends('layouts.buyer')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="ss-page">

    {{-- ── BREADCRUMB ──────────────────────────────────────── --}}
    <nav aria-label="Breadcrumb" style="margin-bottom:var(--sp-4);">
        <ol style="display:flex;align-items:center;gap:8px;list-style:none;font-size:var(--fs-label);color:var(--color-muted);">
            <li><a href="{{ route('catalog.index') }}" style="color:var(--color-sea-teal);font-weight:500;">Beranda</a></li>
            <li aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </li>
            <li aria-current="page" style="color:var(--color-ink);font-weight:600;">Keranjang Belanja</li>
        </ol>
    </nav>

    {{-- ── HEADER HALAMAN ──────────────────────────────────── --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3);">
        <div>
            <h1 class="ss-h2" style="margin-bottom:4px;">Keranjang Belanja Anda</h1>
            <p class="ss-caption">Periksa produk olahan laut pilihan Anda, atur jumlah pesanan, lalu lanjut ke Checkout.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--outline ss-btn--sm">
                &larr; Tambah Produk Lain
            </a>
            <button type="button" class="ss-btn ss-btn--danger-soft ss-btn--sm" id="btnClearCart" style="display:none;">
                Kosongkan Keranjang
            </button>
        </div>
    </div>

    {{-- ── EMPTY STATE ─────────────────────────────────────── --}}
    <div class="ss-surface-card" id="cartEmptyState" style="text-align:center;padding:var(--sp-8) var(--sp-4);display:none;">
        <div style="font-size:52px;margin-bottom:var(--sp-3);" aria-hidden="true">🛒</div>
        <h2 class="ss-h3" style="margin-bottom:var(--sp-1);">Keranjang belanja masih kosong</h2>
        <p class="ss-caption" style="max-width:340px;margin:0 auto var(--sp-5);">
            Yuk jelajahi katalog produk olahan laut segar dari UMKM lokal dan tambahkan ke keranjang Anda.
        </p>
        <a href="{{ route('catalog.index') }}" class="ss-btn ss-btn--primary">
            Jelajahi Katalog Sekarang
        </a>
    </div>

    {{-- ── CART CONTENT (2 KOLOM RESPONSIF) ────────────────── --}}
    <div class="ss-cart-layout" id="cartLayout" style="display:none;">

        {{-- Kolom Kiri: Daftar Item Keranjang --}}
        <div id="cartItemsContainer" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            {{-- Rendered dynamically via JS --}}
        </div>

        {{-- Kolom Kanan: Ringkasan Total Harga --}}
        <aside class="ss-surface-card ss-cart-summary" aria-label="Ringkasan belanja">
            <h2 class="ss-h3" style="margin-bottom:var(--sp-4);padding-bottom:var(--sp-3);border-bottom:1px solid var(--color-border);">
                Ringkasan Belanja
            </h2>

            <div style="display:flex;flex-direction:column;gap:var(--sp-2);margin-bottom:var(--sp-4);">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--color-muted);">Total Jenis Produk</span>
                    <strong id="summaryProductTypes" style="color:var(--color-ink);">0 produk</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--color-muted);">Total Kuantitas</span>
                    <strong id="summaryTotalQty" style="color:var(--color-ink);">0 item</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--color-muted);">Subtotal Harga</span>
                    <strong id="summarySubtotal" style="color:var(--color-ink);">Rp 0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--color-muted);">Biaya Layanan</span>
                    <span style="color:var(--color-success);font-weight:600;">Gratis</span>
                </div>
            </div>

            <div style="padding:var(--sp-3);border-radius:var(--radius-sm);background:var(--color-seafoam-bg);border:1px solid var(--color-seafoam);display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);">
                <span style="font-weight:600;color:var(--color-ocean-navy);">Total Estimasi</span>
                <strong id="summaryGrandTotal" style="font-size:20px;color:var(--color-ocean-navy);">Rp 0</strong>
            </div>

            <button type="button" class="ss-btn ss-btn--primary ss-btn--full ss-btn--lg" id="btnProceedCheckout">
                Lanjut ke Checkout &rarr;
            </button>

            <p class="ss-caption" style="text-align:center;margin-top:var(--sp-3);">
                🔒 Pesanan Anda akan langsung tercatat dan stok otomatis diamankan.
            </p>
        </aside>

    </div>

</div>

{{-- ── MODAL CHECKOUT KERANJANG ────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalCartCheckout" role="dialog" aria-modal="true" aria-labelledby="cartCheckoutTitle">
    <div class="ss-modal" style="max-width:490px;max-height:92vh;overflow-y:auto;">
        <h2 class="ss-modal__title" id="cartCheckoutTitle">📋 Konfirmasi Checkout Keranjang</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">
            Lengkapi alamat penerima untuk menyelesaikan pesanan dari keranjang belanja Anda.
        </p>

        <div id="checkoutSummaryBox" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:var(--sp-3);margin-bottom:var(--sp-4);font-size:13px;">
            {{-- Filled dynamically --}}
        </div>

        <div id="cartCheckoutError" class="ss-alert ss-alert--danger" style="display:none;padding:8px 12px;font-size:12px;margin-bottom:var(--sp-3);" role="alert">
            <span id="cartCheckoutErrorMsg"></span>
        </div>

        <form id="formCartCheckout" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-input-group">
                <label class="ss-label" for="cartRecipientName">Nama Penerima *</label>
                <input type="text" id="cartRecipientName" class="ss-input" value="{{ Auth::user()?->name ?? '' }}" placeholder="Nama lengkap penerima" required>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="cartRecipientPhone">Nomor WhatsApp / Telepon *</label>
                <input type="tel" id="cartRecipientPhone" class="ss-input" value="{{ Auth::user()?->phone ?? '' }}" placeholder="Contoh: 0812-3456-7890" required>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="cartDeliveryAddress">Alamat Pengiriman Lengkap *</label>
                <textarea id="cartDeliveryAddress" class="ss-input" rows="2" style="height:auto;padding:8px var(--sp-3);resize:vertical;" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kota..." required></textarea>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="cartPaymentMethod">Metode Pembayaran</label>
                <select id="cartPaymentMethod" class="ss-select">
                    <option value="Transfer Bank (Menunggu Pembayaran)">Transfer Bank (BCA / BRI / Mandiri)</option>
                    <option value="COD / Bayar di Tempat">COD / Bayar di Tempat</option>
                </select>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="cartBuyerNote">Catatan Pesanan (Opsional)</label>
                <input type="text" id="cartBuyerNote" class="ss-input" placeholder='misal: "packing tambahan es batu"'>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnCancelCartCheckout">Kembali</button>
                <button type="submit" class="ss-btn ss-btn--primary" id="btnConfirmCartCheckout">Buat Pesanan Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveProductsMap = @json($productsMap);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const emptyState = document.getElementById('cartEmptyState');
    const cartLayout = document.getElementById('cartLayout');
    const itemsContainer = document.getElementById('cartItemsContainer');
    const btnClear = document.getElementById('btnClearCart');

    const summaryTypes = document.getElementById('summaryProductTypes');
    const summaryQty = document.getElementById('summaryTotalQty');
    const summarySub = document.getElementById('summarySubtotal');
    const summaryGrand = document.getElementById('summaryGrandTotal');

    function formatRp(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function syncCartWithDB(items) {
        return items.map(item => {
            const live = liveProductsMap[item.id];
            if (!live) return item;
            const maxStock = parseInt(live.stock, 10);
            return {
                ...item,
                name: live.name,
                category: live.category,
                store_name: live.store_name,
                price: parseInt(live.price, 10),
                unit: live.unit,
                stock: maxStock,
                emoji: live.emoji || item.emoji,
                gradient: live.gradient || item.gradient,
                quantity: maxStock > 0 ? Math.min(maxStock, Math.max(1, parseInt(item.quantity, 10) || 1)) : 1
            };
        });
    }

    function renderCart() {
        let items = window.SeaSupplyCart.getItems();
        items = syncCartWithDB(items);
        window.SeaSupplyCart.saveItems(items);

        if (items.length === 0) {
            emptyState.style.display = 'block';
            cartLayout.style.display = 'none';
            btnClear.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        cartLayout.style.display = 'grid';
        btnClear.style.display = 'inline-flex';

        let totalQty = 0;
        let totalAmount = 0;

        itemsContainer.innerHTML = items.map(item => {
            const qty = parseInt(item.quantity, 10) || 1;
            const price = parseInt(item.price, 10) || 0;
            const lineTotal = qty * price;
            const maxStock = parseInt(item.stock, 10) || 99;
            totalQty += qty;
            totalAmount += lineTotal;

            return `
                <article class="ss-cart-item" data-id="${item.id}">
                    <div class="ss-cart-item__main">
                        <div class="ss-cart-item__thumb" style="background:${item.gradient};">
                            <span>${item.emoji || '🐟'}</span>
                        </div>
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:11px;font-weight:600;color:var(--color-sea-teal);text-transform:uppercase;">${item.category}</div>
                            <h3 style="font-size:15px;font-weight:600;color:var(--color-ink);margin:2px 0;">${item.name}</h3>
                            <div class="ss-caption">${item.store_name} &bull; ${formatRp(price)} / ${item.unit}</div>
                            <div class="ss-caption" style="color:${maxStock <= 10 ? 'var(--color-warning)' : 'var(--color-muted)'};margin-top:2px;">
                                Tersedia: ${maxStock} ${item.unit}
                            </div>
                        </div>
                    </div>

                    <div class="ss-cart-item__controls">
                        <div class="ss-qty" aria-label="Ubah jumlah ${item.name}">
                            <button type="button" class="ss-qty__btn btn-cart-dec" data-id="${item.id}" aria-label="Kurangi jumlah">−</button>
                            <div class="ss-qty__value">${qty}</div>
                            <button type="button" class="ss-qty__btn btn-cart-inc" data-id="${item.id}" ${qty >= maxStock ? 'disabled' : ''} aria-label="Tambah jumlah">+</button>
                        </div>

                        <div style="min-width:105px;text-align:right;">
                            <div class="ss-caption">Subtotal</div>
                            <strong style="color:var(--color-ocean-navy);font-size:15px;">${formatRp(lineTotal)}</strong>
                        </div>

                        <button type="button" class="ss-btn ss-btn--danger-soft ss-btn--sm btn-cart-remove" data-id="${item.id}" title="Hapus produk">
                            Hapus
                        </button>
                    </div>
                </article>
            `;
        }).join('');

        summaryTypes.textContent = items.length + ' produk';
        summaryQty.textContent = totalQty + ' item';
        summarySub.textContent = formatRp(totalAmount);
        summaryGrand.textContent = formatRp(totalAmount);
    }

    itemsContainer.addEventListener('click', function (e) {
        const decBtn = e.target.closest('.btn-cart-dec');
        const incBtn = e.target.closest('.btn-cart-inc');
        const remBtn = e.target.closest('.btn-cart-remove');

        if (decBtn) {
            window.SeaSupplyCart.updateQty(decBtn.dataset.id, -1);
            renderCart();
        } else if (incBtn) {
            window.SeaSupplyCart.updateQty(incBtn.dataset.id, 1);
            renderCart();
        } else if (remBtn) {
            window.SeaSupplyCart.removeItem(remBtn.dataset.id);
            renderCart();
            showToast('Produk dihapus dari keranjang.', 'warning');
        }
    });

    btnClear.addEventListener('click', function () {
        if (confirm('Kosongkan seluruh isi keranjang belanja?')) {
            window.SeaSupplyCart.clear();
            renderCart();
        }
    });

    // Modal Checkout
    const modalCheckout = document.getElementById('modalCartCheckout');
    const btnProceed = document.getElementById('btnProceedCheckout');
    const btnCancel = document.getElementById('btnCancelCartCheckout');
    const formCheckout = document.getElementById('formCartCheckout');
    const btnConfirm = document.getElementById('btnConfirmCartCheckout');
    const summaryBox = document.getElementById('checkoutSummaryBox');
    const errorBox = document.getElementById('cartCheckoutError');
    const errorMsg = document.getElementById('cartCheckoutErrorMsg');

    btnProceed.addEventListener('click', function () {
        const items = window.SeaSupplyCart.getItems();
        if (items.length === 0) return;

        const grandTotal = items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        summaryBox.innerHTML = items.map(i => `
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span>${i.name} (${i.quantity} ${i.unit})</span>
                <strong>${formatRp(i.price * i.quantity)}</strong>
            </div>
        `).join('') + `
            <div style="display:flex;justify-content:space-between;padding-top:6px;margin-top:6px;border-top:1px solid var(--color-border);">
                <span style="font-weight:700;">Total Pembayaran</span>
                <strong style="color:var(--color-ocean-navy);font-size:15px;">${formatRp(grandTotal)}</strong>
            </div>
        `;

        errorBox.style.display = 'none';
        modalCheckout.classList.add('open');
    });

    btnCancel.addEventListener('click', () => modalCheckout.classList.remove('open'));
    modalCheckout.addEventListener('click', (e) => {
        if (e.target === modalCheckout) modalCheckout.classList.remove('open');
    });

    formCheckout.addEventListener('submit', async function (e) {
        e.preventDefault();
        const items = window.SeaSupplyCart.getItems();
        if (items.length === 0) return;

        const recipientName = document.getElementById('cartRecipientName').value.trim();
        const recipientPhone = document.getElementById('cartRecipientPhone').value.trim();
        const deliveryAddress = document.getElementById('cartDeliveryAddress').value.trim();
        const paymentMethod = document.getElementById('cartPaymentMethod').value;
        const rawNote = document.getElementById('cartBuyerNote').value.trim();
        const combinedNote = [paymentMethod, rawNote].filter(Boolean).join(' • ');

        btnConfirm.disabled = true;
        btnConfirm.textContent = 'Memproses pesanan...';
        errorBox.style.display = 'none';

        try {
            const createdCodes = [];
            for (const item of items) {
                const res = await fetch('{{ route('catalog.order.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        product_id: item.id,
                        quantity: item.quantity,
                        recipient_name: recipientName,
                        recipient_phone: recipientPhone,
                        delivery_address: deliveryAddress,
                        buyer_note: combinedNote
                    })
                });
                const body = await res.json();
                if (!res.ok) {
                    throw new Error(body.error?.message || body.message || `Gagal memesan ${item.name}`);
                }
                createdCodes.push('#' + body.data.order_code);
            }

            window.SeaSupplyCart.clear();
            modalCheckout.classList.remove('open');
            showToast(`✓ ${createdCodes.length} pesanan (${createdCodes.join(', ')}) berhasil dibuat!`, 'success');
            setTimeout(() => {
                window.location.href = '{{ route('catalog.orders') }}';
            }, 1300);
        } catch (err) {
            errorMsg.textContent = err.message || 'Terjadi kesalahan saat checkout.';
            errorBox.style.display = 'flex';
        } finally {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Buat Pesanan Sekarang';
        }
    });

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--' + (type || 'success');
        toast.textContent = message;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    renderCart();
});
</script>
@endpush
