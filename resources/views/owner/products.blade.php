@extends('layouts.owner')

@section('title', 'Manajemen Produk')
@section('header-title', 'Katalog & Stok Produk Toko')

@section('content')

{{-- ── HEADER & SEARCH / FILTER ────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
    <div>
        <h2 class="ss-h3" style="margin-bottom:2px;">Daftar Produk Olahan Laut</h2>
        <p class="ss-caption">Kelola harga, ketersediaan stok, foto produk, dan visibilitas di katalog pembeli.</p>
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
                    {{-- Produk Visual (Foto Asli / Fallback Emoji) + Nama --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:var(--sp-3);">
                            @if(!empty($p['image_url']))
                                <img src="{{ asset('storage/' . $p['image_url']) }}" alt="{{ $p['name'] }}"
                                     style="width:42px;height:42px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0;border:1px solid var(--color-border);"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display:none;width:42px;height:42px;border-radius:var(--radius-sm);background:{{ $p['gradient'] }};align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                                    {{ $p['emoji'] }}
                                </div>
                            @else
                                <div style="width:42px;height:42px;border-radius:var(--radius-sm);background:{{ $p['gradient'] }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                                    {{ $p['emoji'] }}
                                </div>
                            @endif
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
    <div class="ss-modal" style="max-width:500px;">
        <h2 class="ss-modal__title">Tambah Produk Olahan Laut</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Masukkan informasi produk baru dan unggah foto asli untuk ditampilkan di katalog pembeli.</p>

        <form id="formTambahProduk" style="display:flex;flex-direction:column;gap:var(--sp-3);" enctype="multipart/form-data">
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
                    <label class="ss-label" for="addPrice">Harga Jual (Rp) *</label>
                    <input type="number" class="ss-input" id="addPrice" placeholder="50000" min="0" required>
                </div>
                <div class="ss-input-group">
                    <label class="ss-label" for="addStock">Stok Awal *</label>
                    <input type="number" class="ss-input" id="addStock" placeholder="25" min="0" required>
                </div>
            </div>

            {{-- Form Upload Foto Produk Asli --}}
            <div class="ss-input-group">
                <label class="ss-label" for="addImage">Foto Produk Asli (Opsional)</label>
                <input type="file" class="ss-input" id="addImage" accept="image/png, image/jpeg, image/jpg, image/webp" style="padding:6px var(--sp-2);">
                <span class="ss-caption" style="color:var(--color-muted);">Format: JPG, PNG, WEBP (Maksimal 2MB)</span>
                
                {{-- Pratinjau Foto Produk --}}
                <div id="addPreviewContainer" style="display:none;margin-top:8px;position:relative;width:fit-content;">
                    <img id="addPreviewImg" src="" alt="Pratinjau" style="width:72px;height:72px;object-fit:cover;border-radius:var(--radius-sm);border:1.5px solid var(--color-sea-teal);">
                    <button type="button" id="btnCancelAddImg" style="position:absolute;top:-6px;right:-6px;background:var(--color-danger);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;" title="Hapus foto">&times;</button>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="addDesc">Deskripsi Produk</label>
                <textarea class="ss-input" id="addDesc" rows="3" style="height:auto;padding:8px var(--sp-3);resize:vertical;" placeholder="Keterangan rasa, ketahanan simpan, atau cara saji..."></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalTambah">Batal</button>
                <button type="submit" class="ss-btn ss-btn--navy" id="btnSubmitTambah">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT PRODUK ───────────────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalEditProduk" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:500px;">
        <h2 class="ss-modal__title">Ubah Produk</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Perbarui harga, ketersediaan stok, foto produk, atau deskripsi produk.</p>

        <form id="formEditProduk" style="display:flex;flex-direction:column;gap:var(--sp-3);" enctype="multipart/form-data">
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

            {{-- Form Upload Foto Edit Produk --}}
            <div class="ss-input-group">
                <label class="ss-label" for="editImage">Foto Produk Asli</label>
                
                {{-- Foto Saat Ini --}}
                <div id="editCurrentImageContainer" style="display:none;margin-bottom:8px;align-items:center;gap:var(--sp-3);background:var(--color-surface);padding:8px;border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                    <img id="editCurrentImg" src="" alt="Foto saat ini" style="width:50px;height:50px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                    <div>
                        <div class="ss-caption" style="font-weight:600;color:var(--color-ink);">Foto Saat Ini</div>
                        <span class="ss-caption" style="color:var(--color-muted);font-size:10px;">Pilih file di bawah jika ingin mengganti</span>
                    </div>
                </div>

                <input type="file" class="ss-input" id="editImage" accept="image/png, image/jpeg, image/jpg, image/webp" style="padding:6px var(--sp-2);">
                <span class="ss-caption" style="color:var(--color-muted);">Pilih file baru untuk mengganti foto (Maks. 2MB)</span>

                {{-- Pratinjau Foto Baru --}}
                <div id="editPreviewContainer" style="display:none;margin-top:8px;position:relative;width:fit-content;">
                    <img id="editPreviewImg" src="" alt="Pratinjau Baru" style="width:72px;height:72px;object-fit:cover;border-radius:var(--radius-sm);border:1.5px solid var(--color-sea-teal);">
                    <button type="button" id="btnCancelEditImg" style="position:absolute;top:-6px;right:-6px;background:var(--color-danger);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;" title="Batal ganti">&times;</button>
                </div>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="editDesc">Deskripsi</label>
                <textarea class="ss-input" id="editDesc" rows="3" style="height:auto;padding:8px var(--sp-3);resize:vertical;"></textarea>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalEdit">Batal</button>
                <button type="submit" class="ss-btn ss-btn--primary" id="btnSubmitEdit">Simpan Perubahan</button>
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

    // ── 1. Tambah Produk ────────────────────────────────────────────
    const modalTambah        = document.getElementById('modalTambahProduk');
    const btnTambah          = document.getElementById('btnTambahProduk');
    const btnBatalTambah     = document.getElementById('btnBatalTambah');
    const formTambah         = document.getElementById('formTambahProduk');
    const inputAddImg        = document.getElementById('addImage');
    const addPreviewCont     = document.getElementById('addPreviewContainer');
    const addPreviewImg      = document.getElementById('addPreviewImg');
    const btnCancelAddImg    = document.getElementById('btnCancelAddImg');
    const btnSubmitTambah    = document.getElementById('btnSubmitTambah');

    btnTambah.addEventListener('click', () => modalTambah.classList.add('open'));
    btnBatalTambah.addEventListener('click', () => {
        modalTambah.classList.remove('open');
        resetAddImgPreview();
    });

    // Pratinjau gambar saat memilih file
    inputAddImg.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                addPreviewImg.src = e.target.result;
                addPreviewCont.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            resetAddImgPreview();
        }
    });

    btnCancelAddImg.addEventListener('click', resetAddImgPreview);

    function resetAddImgPreview() {
        inputAddImg.value = '';
        addPreviewImg.src = '';
        addPreviewCont.style.display = 'none';
    }

    formTambah.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSubmitTambah.disabled = true;

        const formData = new FormData();
        formData.append('name', document.getElementById('addName').value);
        formData.append('category', document.getElementById('addCategory').value);
        formData.append('unit', document.getElementById('addUnit').value);
        formData.append('price', parseInt(document.getElementById('addPrice').value));
        formData.append('stock', parseInt(document.getElementById('addStock').value));
        formData.append('description', document.getElementById('addDesc').value);

        if (inputAddImg.files.length > 0) {
            formData.append('image', inputAddImg.files[0]);
        }

        fetch('{{ route('owner.products.store') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => {
            btnSubmitTambah.disabled = false;
            modalTambah.classList.remove('open');
            formTambah.reset();
            resetAddImgPreview();
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            btnSubmitTambah.disabled = false;
            const errMsg = err.message || (err.errors ? Object.values(err.errors)[0][0] : 'Gagal menyimpan produk.');
            alert('Kesalahan: ' + errMsg);
        });
    });

    // ── 2. Edit Produk ──────────────────────────────────────────────
    const modalEdit             = document.getElementById('modalEditProduk');
    const btnBatalEdit          = document.getElementById('btnBatalEdit');
    const formEdit              = document.getElementById('formEditProduk');
    const inputEditImg          = document.getElementById('editImage');
    const editPreviewCont       = document.getElementById('editPreviewContainer');
    const editPreviewImg        = document.getElementById('editPreviewImg');
    const btnCancelEditImg      = document.getElementById('btnCancelEditImg');
    const editCurrentCont       = document.getElementById('editCurrentImageContainer');
    const editCurrentImg        = document.getElementById('editCurrentImg');
    const btnSubmitEdit         = document.getElementById('btnSubmitEdit');

    document.querySelectorAll('.btn-edit-product').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.product);
            document.getElementById('editId').value    = data.id;
            document.getElementById('editName').value  = data.name;
            document.getElementById('editPrice').value = data.price;
            document.getElementById('editStock').value = data.stock;
            document.getElementById('editDesc').value  = data.description || '';

            // Tampilkan foto saat ini jika ada
            if (data.image_url) {
                editCurrentImg.src = '/storage/' + data.image_url;
                editCurrentCont.style.display = 'flex';
            } else {
                editCurrentImg.src = '';
                editCurrentCont.style.display = 'none';
            }

            resetEditImgPreview();
            modalEdit.classList.add('open');
        });
    });

    inputEditImg.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                editPreviewImg.src = e.target.result;
                editPreviewCont.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            resetEditImgPreview();
        }
    });

    btnCancelEditImg.addEventListener('click', resetEditImgPreview);

    function resetEditImgPreview() {
        inputEditImg.value = '';
        editPreviewImg.src = '';
        editPreviewCont.style.display = 'none';
    }

    btnBatalEdit.addEventListener('click', () => {
        modalEdit.classList.remove('open');
        resetEditImgPreview();
    });

    formEdit.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSubmitEdit.disabled = true;

        const id = document.getElementById('editId').value;
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('name', document.getElementById('editName').value);
        formData.append('price', parseInt(document.getElementById('editPrice').value));
        formData.append('stock', parseInt(document.getElementById('editStock').value));
        formData.append('description', document.getElementById('editDesc').value);

        if (inputEditImg.files.length > 0) {
            formData.append('image', inputEditImg.files[0]);
        }

        fetch('/owner/products/' + id, {
            method: 'POST', // Dengan _method PATCH untuk multipart FormData di Laravel
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => {
            btnSubmitEdit.disabled = false;
            modalEdit.classList.remove('open');
            resetEditImgPreview();
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            btnSubmitEdit.disabled = false;
            const errMsg = err.message || (err.errors ? Object.values(err.errors)[0][0] : 'Gagal memperbarui produk.');
            alert('Kesalahan: ' + errMsg);
        });
    });

    // ── 3. Hapus Produk (Design System Modal) ────────────────────────
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
