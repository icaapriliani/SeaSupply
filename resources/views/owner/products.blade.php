@extends('layouts.owner')

@section('title', 'Manajemen Produk')
@section('header-title', 'Katalog & Stok Produk Toko')

@section('content')

{{-- ── HEADER & SEARCH / FILTER ────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
    <div>
        <h2 class="ss-h3" style="margin-bottom:2px;">Daftar Produk Olahan Laut</h2>
        <p class="ss-caption">Kelola harga, ketersediaan stok, dan visibilitas produk di katalog pembeli.</p>
    </div>

    <button class="ss-btn ss-btn--navy" id="btnTambahProduk">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Produk Baru
    </button>
</div>

{{-- ── TABEL PRODUK TOKO ───────────────────────────────────────────── --}}
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr id="rowProduct-{{ $p['id'] }}">
                    {{-- Produk Visual + Nama --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:var(--sp-3);">
                            <div style="width:40px;height:40px;border-radius:var(--radius-sm);background:{{ $p['gradient'] }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                                {{ $p['emoji'] }}
                            </div>
                            <div>
                                <strong style="color:var(--color-ink);">{{ $p['name'] }}</strong>
                                <div class="ss-caption" style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $p['description'] ?: 'Tidak ada deskripsi.' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kategori --}}
                    <td>
                        <span class="ss-chip" style="padding:2px 8px;font-size:11px;pointer-events:none;">
                            {{ $p['category'] }}
                        </span>
                    </td>

                    {{-- Harga --}}
                    <td>
                        <strong>Rp {{ number_format($p['price'], 0, ',', '.') }}</strong>
                        <span class="ss-caption">/ {{ $p['unit'] }}</span>
                    </td>

                    {{-- Stok --}}
                    <td>
                        <strong style="{{ $p['stock'] === 0 ? 'color:var(--color-danger);' : ($p['stock'] <= 10 ? 'color:var(--color-warning);' : 'color:var(--color-ink);') }}">
                            {{ $p['stock'] }} {{ $p['unit'] }}
                        </strong>
                    </td>

                    {{-- Status Badge --}}
                    <td>
                        @if(!$p['is_active'])
                            <span class="ss-badge ss-badge--cancelled">Nonaktif</span>
                        @elseif($p['stock'] === 0)
                            <span class="ss-badge ss-badge--out-of-stock">Stok Habis</span>
                        @elseif($p['stock'] <= 10)
                            <span class="ss-badge ss-badge--pending">Menipis</span>
                        @else
                            <span class="ss-badge ss-badge--available">Aktif</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                            <button class="ss-btn ss-btn--outline ss-btn--sm btn-edit-product"
                                    data-product="{{ json_encode($p) }}">
                                Edit
                            </button>
                            <button class="ss-btn ss-btn--danger-soft ss-btn--sm btn-delete-product"
                                    data-id="{{ $p['id'] }}"
                                    data-name="{{ $p['name'] }}">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:var(--sp-7);color:var(--color-muted);">
                        Belum ada produk. Silakan tambahkan produk baru.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- ── MODAL TAMBAH PRODUK ─────────────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalTambahProduk" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:480px;">
        <h2 class="ss-modal__title">Tambah Produk Olahan Laut</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Masukkan informasi produk baru untuk ditampilkan di katalog pembeli.</p>

        <form id="formTambahProduk" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-input-group">
                <label class="ss-label" for="addName">Nama Produk *</label>
                <input type="text" class="ss-input" id="addName" placeholder="Contoh: Olahan Rajungan Pedas" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                <div class="ss-input-group">
                    <label class="ss-label" for="addCategory">Kategori *</label>
                    <select class="ss-select" id="addCategory" required>
                        <option value="Rajungan">Rajungan</option>
                        <option value="Ikan Kering">Ikan Kering</option>
                        <option value="Kerupuk Ikan">Kerupuk Ikan</option>
                        <option value="Udang Olahan">Udang Olahan</option>
                        <option value="Cumi-cumi">Cumi-cumi</option>
                        <option value="Kepiting">Kepiting</option>
                    </select>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="addUnit">Satuan</label>
                    <input type="text" class="ss-input" id="addUnit" value="kg" placeholder="kg, bungkus, pak">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                <div class="ss-input-group">
                    <label class="ss-label" for="addPrice">Harga (Rp) *</label>
                    <input type="number" class="ss-input" id="addPrice" placeholder="50000" min="0" required>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="addStock">Stok Awal *</label>
                    <input type="number" class="ss-input" id="addStock" placeholder="25" min="0" required>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="addDesc">Deskripsi Produk</label>
                <textarea class="ss-input" id="addDesc" rows="3" style="height:auto;padding:8px var(--sp-3);resize:vertical;" placeholder="Keterangan rasa, ketahanan simpan, atau cara saji..."></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalTambah">Batal</button>
                <button type="submit" class="ss-btn ss-btn--navy">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT PRODUK ───────────────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalEditProduk" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:480px;">
        <h2 class="ss-modal__title">Ubah Produk</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Perbarui harga, ketersediaan stok, atau status keaktifan produk.</p>

        <form id="formEditProduk" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <input type="hidden" id="editId">

            <div class="ss-input-group">
                <label class="ss-label" for="editName">Nama Produk *</label>
                <input type="text" class="ss-input" id="editName" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                <div class="ss-input-group">
                    <label class="ss-label" for="editPrice">Harga Jual (Rp) *</label>
                    <input type="number" class="ss-input" id="editPrice" min="0" required>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="editStock">Stok Saat Ini *</label>
                    <input type="number" class="ss-input" id="editStock" min="0" required>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="editDesc">Deskripsi</label>
                <textarea class="ss-input" id="editDesc" rows="3" style="height:auto;padding:8px var(--sp-3);resize:vertical;"></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalEdit">Batal</button>
                <button type="submit" class="ss-btn ss-btn--primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL HAPUS PRODUK (Sesuai Design System Bagian 12) ────────── --}}
<div class="ss-modal-overlay" id="modalHapusProduk" role="dialog" aria-modal="true">
    <div class="ss-modal">
        <h2 class="ss-modal__title">Hapus produk?</h2>
        <p class="ss-modal__body">
            Produk <strong id="hapusNamaProduk"></strong> akan dihapus dari katalog pembeli. Histori transaksi pesanan lama tetap aman tersimpan.
        </p>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--outline" id="btnBatalHapus">Batal</button>
            <button type="button" class="ss-btn ss-btn--danger" id="btnKonfirmasiHapus">Hapus</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Tambah Produk
    const modalTambah    = document.getElementById('modalTambahProduk');
    const btnTambah      = document.getElementById('btnTambahProduk');
    const btnBatalTambah = document.getElementById('btnBatalTambah');
    const formTambah     = document.getElementById('formTambahProduk');

    btnTambah.addEventListener('click', () => modalTambah.classList.add('open'));
    btnBatalTambah.addEventListener('click', () => modalTambah.classList.remove('open'));

    formTambah.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch('{{ route('owner.products.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                name: document.getElementById('addName').value,
                category: document.getElementById('addCategory').value,
                unit: document.getElementById('addUnit').value,
                price: parseInt(document.getElementById('addPrice').value),
                stock: parseInt(document.getElementById('addStock').value),
                description: document.getElementById('addDesc').value
            })
        })
        .then(res => res.json())
        .then(data => {
            modalTambah.classList.remove('open');
            formTambah.reset();
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        });
    });

    // 2. Edit Produk
    const modalEdit    = document.getElementById('modalEditProduk');
    const btnBatalEdit = document.getElementById('btnBatalEdit');
    const formEdit     = document.getElementById('formEditProduk');

    document.querySelectorAll('.btn-edit-product').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.product);
            document.getElementById('editId').value    = data.id;
            document.getElementById('editName').value  = data.name;
            document.getElementById('editPrice').value = data.price;
            document.getElementById('editStock').value = data.stock;
            document.getElementById('editDesc').value  = data.description || '';
            modalEdit.classList.add('open');
        });
    });

    btnBatalEdit.addEventListener('click', () => modalEdit.classList.remove('open'));

    formEdit.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('editId').value;
        fetch('/owner/products/' + id, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                name: document.getElementById('editName').value,
                price: parseInt(document.getElementById('editPrice').value),
                stock: parseInt(document.getElementById('editStock').value),
                description: document.getElementById('editDesc').value
            })
        })
        .then(res => res.json())
        .then(data => {
            modalEdit.classList.remove('open');
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        });
    });

    // 3. Hapus Produk (Design System Modal)
    const modalHapus         = document.getElementById('modalHapusProduk');
    const btnBatalHapus      = document.getElementById('btnBatalHapus');
    const btnKonfirmasiHapus = document.getElementById('btnKonfirmasiHapus');
    let targetDeleteId       = null;

    document.querySelectorAll('.btn-delete-product').forEach(btn => {
        btn.addEventListener('click', function () {
            targetDeleteId = this.dataset.id;
            document.getElementById('hapusNamaProduk').textContent = `"${this.dataset.name}"`;
            modalHapus.classList.add('open');
        });
    });

    btnBatalHapus.addEventListener('click', () => modalHapus.classList.remove('open'));

    btnKonfirmasiHapus.addEventListener('click', function () {
        if (!targetDeleteId) return;
        btnKonfirmasiHapus.disabled = true;

        fetch('/owner/products/' + targetDeleteId, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(res => res.json())
        .then(data => {
            btnKonfirmasiHapus.disabled = false;
            modalHapus.classList.remove('open');
            showToast('✓ ' + data.message, 'success');
            const row = document.getElementById('rowProduct-' + targetDeleteId);
            if (row) row.remove();
        })
        .catch(err => {
            btnKonfirmasiHapus.disabled = false;
            alert('Gagal menghapus produk.');
        });
    });

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'ss-toast ss-toast--' + (type || 'success');
        toast.textContent = message;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }
});
</script>
@endpush
