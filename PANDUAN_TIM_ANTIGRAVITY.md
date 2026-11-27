# 🚀 Panduan Setup & Prompt Antigravity untuk Tim SeaSupply

Panduan ini ditujukan untuk **Lely** (`fitur-lely`), **Sherly** (`fitur-sherly`), dan anggota tim lainnya agar seluruh kode dan instruksi AI di **Google Antigravity** berjalan selaras, konsisten, dan tidak terjadi konflik.

---

## TAHAP 1: Setup Proyek di Laptop Masing-Masing (Laragon)

Jalankan perintah ini di terminal (Laragon):

### A. Jika Belum Pernah Clone:
```bash
# 1. Clone repository
git clone https://github.com/icaapriliani/SeaSupply.git
cd SeaSupply

# 2. Pindah ke branch tugas Anda (contoh untuk Lely):
git checkout fitur-lely
# (atau untuk Sherly: git checkout fitur-sherly)

# 3. Gabungkan pembaruan terakhir dari main:
git merge origin/main
```

### B. Jika Sudah Pernah Clone Sebelumnya:
```bash
# Ambil semua update terbaru dari branch main
git checkout main
git pull origin main

# Pindah ke branch Anda dan gabungkan update main
git checkout fitur-lely       # (atau fitur-sherly)
git merge main
```

---

## TAHAP 2: Setup Database MySQL di Laragon

1. Buka **Laragon** &rarr; klik **"Start All"**.
2. Di terminal proyek, jalankan:
```bash
# 1. Copy environment
cp .env.example .env

# 2. Install dependensi
composer install
npm install

# 3. Generate key
php artisan key:generate

# 4. Bangun database otomatis di Laragon
php artisan migrate --seed
# (Jika muncul pertanyaan "Database 'seasupply' does not exist. Would you like to create it?", tekan Enter / yes)

# 5. Build asset & jalankan server
npm run build
php artisan serve
```

Aplikasi langsung aktif di `http://127.0.0.1:8000`.

---

## TAHAP 3: Standarisasi Prompt Antigravity

Agar AI Agent di Antigravity teman Anda **tidak mengubah desain sembarangan, tidak mengganti database, dan menghasilkan kode yang konsisten**, setiap kali memulai sesi Antigravity baru, gunakan **Template Prompt Pembuka** berikut:

### 📋 Salin Template Prompt Ini ke Antigravity:

```text
Halo Antigravity, saya sedang mengerjakan project "SeaSupply — Marketplace UMKM Olahan Laut" berbasis Laravel 11/12 + Blade + Vite + Tailwind + MySQL (Laragon).

Aturan Wajib Proyek yang Harus Kamu Ikuti:
1. Baca file acuan arsitektur di: SeaSupply_Backend_AI_Agent_Guide.md.
2. Gunakan Design System SeaSupply yang sudah ada di resources/css/app.css:
   - Warna Utama: Ocean Navy (#123B4A), Sea Teal (#168C83), Seafoam (#83C5BE), Surface (#F7F9F8).
   - Jangan mengganti warna, jangan merombak layout yang sudah ada.
3. Database menggunakan MySQL (seasupply) dengan tabel: stores, store_memberships, categories, products, orders, order_items, users.
4. Sistem memiliki 3 peran:
   - Buyer: /catalog, /catalog/orders
   - Owner: /owner/dashboard, /owner/products, /owner/orders, /owner/finance, /owner/employees
   - Employee: /employee/orders, /employee/summary, /employee/products
5. Jaga proteksi middleware 'store.role' dan jangan merusak fungsionalitas yang sudah ada.

TUGAS SAYA PADA BRANCH INI ADALAH:
[TULISKAN TUGAS ANDA DI SINI, contoh: Menambahkan form upload foto produk di modal tambah produk Owner]

Langkah awal: Periksa kode yang relevan dan jelaskan rencanamu sebelum menulis kode.
```

---

## TAHAP 4: Alur Commit & Kirim Kode ke GitHub

Setelah selesai mengerjakan fitur:

```bash
# 1. Cek file yang berubah
git status

# 2. Simpan dan commit
git add .
git commit -m "feat(nama-fitur): penjelasan singkat apa yang dibuat"

# 3. Upload ke GitHub branch Anda
git push origin fitur-lely       # (atau fitur-sherly)
```

Setelah di-push:
1. Buka repository GitHub: `https://github.com/icaapriliani/SeaSupply`.
2. Klik tombol **"Compare & pull request"** dari branch Anda ke branch `main`.
3. Diskusikan dan klik **"Merge pull request"**.
