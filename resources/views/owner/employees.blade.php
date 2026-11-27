@extends('layouts.owner')

@section('title', 'Kelola Pegawai')
@section('header-title', 'Pengelolaan Pegawai & Hak Akses Toko')

@section('content')
<section class="ss-surface-card" style="margin-bottom:var(--sp-6);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-4);flex-wrap:wrap;gap:var(--sp-3);">
        <div>
            <h2 class="ss-h3" style="margin-bottom:2px;">Daftar Pegawai Toko</h2>
            <p class="ss-caption">Atur peran, kata sandi, dan hak akses operasional pegawai sesuai kebutuhan toko.</p>
        </div>
        <button class="ss-btn ss-btn--navy ss-btn--sm" id="btnTambahPegawai">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Pegawai Baru
        </button>
    </div>

    <div class="ss-table-wrapper">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Nama & Email</th>
                    <th>Peran</th>
                    <th>Hak Akses (Permissions)</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th style="text-align:right;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr id="rowEmployee-{{ $emp['id'] }}">
                    <td>
                        <strong style="color:var(--color-ink);">{{ $emp['name'] }}</strong>
                        <div class="ss-caption">{{ $emp['email'] }}</div>
                    </td>
                    <td>
                        <span class="ss-chip" style="padding:2px 8px;font-size:11px;pointer-events:none;">
                            {{ $emp['role'] }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @forelse($emp['permissions'] as $p)
                                @if($p === 'orders.read')
                                    <span class="ss-badge ss-badge--processing" style="font-size:10px;">Lihat Pesanan</span>
                                @elseif($p === 'orders.update_status')
                                    <span class="ss-badge ss-badge--available" style="font-size:10px;">Update Status</span>
                                @elseif($p === 'products.read')
                                    <span class="ss-badge ss-badge--pending" style="font-size:10px;">Lihat Produk</span>
                                @else
                                    <span class="ss-badge" style="font-size:10px;">{{ $p }}</span>
                                @endif
                            @empty
                                <span class="ss-caption" style="color:var(--color-muted);">Tidak ada izin</span>
                            @endforelse
                        </div>
                    </td>
                    <td>
                        @if(strtolower($emp['status']) === 'active')
                            <span class="ss-badge ss-badge--completed">Aktif</span>
                        @else
                            <span class="ss-badge ss-badge--cancelled">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <span class="ss-caption">{{ $emp['created_at'] }}</span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                            <button class="ss-btn ss-btn--outline ss-btn--sm btn-edit-employee"
                                    data-employee="{{ json_encode($emp) }}">
                                Atur Izin
                            </button>
                            <button class="ss-btn ss-btn--danger-soft ss-btn--sm btn-delete-employee"
                                    data-id="{{ $emp['id'] }}"
                                    data-name="{{ $emp['name'] }}">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:var(--sp-7);color:var(--color-muted);">
                        Belum ada pegawai yang terdaftar. Klik tombol di atas untuk menambah pegawai baru.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:var(--sp-5);">
        <a href="{{ route('owner.dashboard') }}" class="ss-btn ss-btn--outline ss-btn--sm">
            &larr; Kembali ke Dashboard
        </a>
    </div>
</section>

{{-- ── MODAL TAMBAH PEGAWAI BARU ──────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalTambahPegawai" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:500px;">
        <h2 class="ss-modal__title">Tambah Pegawai Toko Baru</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Buat akun baru untuk staf operasional dan tentukan hak akses yang diizinkan.</p>

        <form id="formTambahPegawai" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <div class="ss-input-group">
                <label class="ss-label" for="empName">Nama Lengkap Pegawai *</label>
                <input type="text" class="ss-input" id="empName" placeholder="Contoh: Budi Santoso" required>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="empEmail">Alamat Email *</label>
                <input type="email" class="ss-input" id="empEmail" placeholder="pegawai@seasupply.local" required>
            </div>

            <div class="ss-input-group">
                <label class="ss-label" for="empPassword">Kata Sandi Awal *</label>
                <input type="password" class="ss-input" id="empPassword" placeholder="Minimal 6 karakter" minlength="6" required>
                <span class="ss-caption" style="color:var(--color-muted);">Kata sandi ini digunakan pegawai untuk login pertama kali.</span>
            </div>

            {{-- Checklist Hak Akses Pegawai --}}
            <div class="ss-input-group" style="margin-top:var(--sp-2);">
                <label class="ss-label" style="margin-bottom:var(--sp-2);display:block;">Pilih Hak Akses (Permissions) *</label>
                
                <div style="display:flex;flex-direction:column;gap:8px;background:var(--color-surface);padding:var(--sp-3);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="permissions[]" value="orders.read" checked style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">orders.read (Melihat Pesanan)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Pegawai dapat melihat daftar pesanan masuk dan detailnya.</div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;border-top:1px solid var(--color-border);padding-top:8px;">
                        <input type="checkbox" name="permissions[]" value="orders.update_status" checked style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">orders.update_status (Ubah Status Pesanan)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Pegawai dapat memperbarui status pesanan (Proses, Kirim, Selesai, Batalkan).</div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;border-top:1px solid var(--color-border);padding-top:8px;">
                        <input type="checkbox" name="permissions[]" value="products.read" checked style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">products.read (Melihat Stok Produk)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Pegawai dapat memeriksa ketersediaan stok produk olahan laut.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalTambahPegawai">Batal</button>
                <button type="submit" class="ss-btn ss-btn--navy" id="btnSubmitTambahPegawai">Simpan Pegawai</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL ATUR IZIN PEGAWAI ─────────────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalEditPegawai" role="dialog" aria-modal="true">
    <div class="ss-modal" style="max-width:500px;">
        <h2 class="ss-modal__title">Atur Hak Akses Pegawai</h2>
        <p class="ss-caption" style="margin-bottom:var(--sp-4);">Sesuaikan hak akses operasional dan status untuk <strong id="editEmpNameLabel"></strong>.</p>

        <form id="formEditPegawai" style="display:flex;flex-direction:column;gap:var(--sp-3);">
            <input type="hidden" id="editEmpId">

            <div class="ss-input-group">
                <label class="ss-label" for="editEmpStatus">Status Akun</label>
                <select class="ss-select" id="editEmpStatus">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <div class="ss-input-group" style="margin-top:var(--sp-2);">
                <label class="ss-label" style="margin-bottom:var(--sp-2);display:block;">Hak Akses (Permissions)</label>
                
                <div style="display:flex;flex-direction:column;gap:8px;background:var(--color-surface);padding:var(--sp-3);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" id="editPermOrdersRead" value="orders.read" style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">orders.read (Melihat Pesanan)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Melihat daftar dan rincian pesanan pembeli.</div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;border-top:1px solid var(--color-border);padding-top:8px;">
                        <input type="checkbox" id="editPermOrdersUpdate" value="orders.update_status" style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">orders.update_status (Ubah Status Pesanan)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Memproses, mengirim, menyelesaikan, atau membatalkan pesanan.</div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;border-top:1px solid var(--color-border);padding-top:8px;">
                        <input type="checkbox" id="editPermProductsRead" value="products.read" style="margin-top:3px;accent-color:var(--color-sea-teal);">
                        <div>
                            <strong style="color:var(--color-ocean-navy);">products.read (Melihat Stok Produk)</strong>
                            <div class="ss-caption" style="color:var(--color-muted);">Memeriksa persediaan dan katalog produk toko.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="ss-modal__actions" style="margin-top:var(--sp-3);">
                <button type="button" class="ss-btn ss-btn--outline" id="btnBatalEditPegawai">Batal</button>
                <button type="submit" class="ss-btn ss-btn--primary" id="btnSubmitEditPegawai">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL KONFIRMASI HAPUS PEGAWAI ──────────────────────────────── --}}
<div class="ss-modal-overlay" id="modalHapusPegawai" role="dialog" aria-modal="true">
    <div class="ss-modal">
        <h2 class="ss-modal__title">Hapus pegawai toko?</h2>
        <p class="ss-modal__body">
            Akses pegawai <strong id="hapusNamaPegawai"></strong> ke panel operasional toko akan dicabut. Riwayat tindakan pesanan terdahulu tetap tercatat.
        </p>

        <div class="ss-modal__actions">
            <button type="button" class="ss-btn ss-btn--outline" id="btnBatalHapusPegawai">Batal</button>
            <button type="button" class="ss-btn ss-btn--danger" id="btnKonfirmasiHapusPegawai">Hapus Pegawai</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ── 1. Tambah Pegawai ───────────────────────────────────────────
    const modalTambah     = document.getElementById('modalTambahPegawai');
    const btnTambah       = document.getElementById('btnTambahPegawai');
    const btnBatalTambah  = document.getElementById('btnBatalTambahPegawai');
    const formTambah      = document.getElementById('formTambahPegawai');
    const btnSubmitTambah = document.getElementById('btnSubmitTambahPegawai');

    btnTambah.addEventListener('click', () => modalTambah.classList.add('open'));
    btnBatalTambah.addEventListener('click', () => modalTambah.classList.remove('open'));

    formTambah.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSubmitTambah.disabled = true;

        const checkedPerms = Array.from(document.querySelectorAll('#formTambahPegawai input[name="permissions[]"]:checked'))
            .map(cb => cb.value);

        fetch('{{ route('owner.employees.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: document.getElementById('empName').value,
                email: document.getElementById('empEmail').value,
                password: document.getElementById('empPassword').value,
                permissions: checkedPerms
            })
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => {
            btnSubmitTambah.disabled = false;
            modalTambah.classList.remove('open');
            formTambah.reset();
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            btnSubmitTambah.disabled = false;
            const errMsg = err.message || (err.errors ? Object.values(err.errors)[0][0] : 'Gagal menambahkan pegawai.');
            alert('Kesalahan: ' + errMsg);
        });
    });

    // ── 2. Atur Izin / Edit Pegawai ──────────────────────────────────
    const modalEdit         = document.getElementById('modalEditPegawai');
    const btnBatalEdit      = document.getElementById('btnBatalEditPegawai');
    const formEdit          = document.getElementById('formEditPegawai');
    const editEmpNameLabel  = document.getElementById('editEmpNameLabel');
    const btnSubmitEdit     = document.getElementById('btnSubmitEditPegawai');

    document.querySelectorAll('.btn-edit-employee').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.employee);
            document.getElementById('editEmpId').value = data.id;
            editEmpNameLabel.textContent = `"${data.name}" (${data.email})`;
            document.getElementById('editEmpStatus').value = (data.status || 'active').toLowerCase();

            const perms = data.permissions || [];
            document.getElementById('editPermOrdersRead').checked   = perms.includes('orders.read');
            document.getElementById('editPermOrdersUpdate').checked = perms.includes('orders.update_status');
            document.getElementById('editPermProductsRead').checked = perms.includes('products.read');

            modalEdit.classList.add('open');
        });
    });

    btnBatalEdit.addEventListener('click', () => modalEdit.classList.remove('open'));

    formEdit.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSubmitEdit.disabled = true;

        const empId = document.getElementById('editEmpId').value;
        const selectedPerms = [];
        if (document.getElementById('editPermOrdersRead').checked) selectedPerms.push('orders.read');
        if (document.getElementById('editPermOrdersUpdate').checked) selectedPerms.push('orders.update_status');
        if (document.getElementById('editPermProductsRead').checked) selectedPerms.push('products.read');

        fetch('/owner/employees/' + empId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                status: document.getElementById('editEmpStatus').value,
                permissions: selectedPerms
            })
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err; });
            return res.json();
        })
        .then(data => {
            btnSubmitEdit.disabled = false;
            modalEdit.classList.remove('open');
            showToast('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            btnSubmitEdit.disabled = false;
            const errMsg = err.message || (err.errors ? Object.values(err.errors)[0][0] : 'Gagal memperbarui izin pegawai.');
            alert('Kesalahan: ' + errMsg);
        });
    });

    // ── 3. Hapus Pegawai ────────────────────────────────────────────
    const modalHapus         = document.getElementById('modalHapusPegawai');
    const btnBatalHapus      = document.getElementById('btnBatalHapusPegawai');
    const btnKonfirmasiHapus = document.getElementById('btnKonfirmasiHapusPegawai');
    let targetDeleteEmpId    = null;

    document.querySelectorAll('.btn-delete-employee').forEach(btn => {
        btn.addEventListener('click', function () {
            targetDeleteEmpId = this.dataset.id;
            document.getElementById('hapusNamaPegawai').textContent = `"${this.dataset.name}"`;
            modalHapus.classList.add('open');
        });
    });

    btnBatalHapus.addEventListener('click', () => modalHapus.classList.remove('open'));

    btnKonfirmasiHapus.addEventListener('click', function () {
        if (!targetDeleteEmpId) return;
        btnKonfirmasiHapus.disabled = true;

        fetch('/owner/employees/' + targetDeleteEmpId, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            btnKonfirmasiHapus.disabled = false;
            modalHapus.classList.remove('open');
            showToast('✓ ' + data.message, 'success');
            const row = document.getElementById('rowEmployee-' + targetDeleteEmpId);
            if (row) row.remove();
        })
        .catch(err => {
            btnKonfirmasiHapus.disabled = false;
            alert('Gagal menghapus pegawai.');
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
