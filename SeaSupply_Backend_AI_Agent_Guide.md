# SeaSupply — Backend Implementation Guide untuk AI Agent

> **Dokumen acuan implementasi backend** berdasarkan dua artefak desain: **SeaSupply — Design System** dan **02 — UX Flow & Wireframe**.  
> Gunakan dokumen ini sebagai konteks utama saat menganalisis repository, merancang database, membuat API, menerapkan autentikasi dan otorisasi, serta menulis pengujian backend.

---

## 0. Instruksi Utama untuk AI Agent

Anda bertindak sebagai **Senior Backend Engineer** yang mengimplementasikan fitur aplikasi **SeaSupply**, sebuah marketplace UMKM olahan laut. Implementasi wajib mengikuti kebutuhan dan batasan yang dijelaskan dalam dokumen ini.

### Prioritas kerja:
1. Ikuti stack Laravel + Blade + Vite + Tailwind yang sudah ada di repository.
2. Gunakan **SeaSupply Design System** (warna Ocean Navy `#123B4A`, Sea Teal `#168C83`, Seafoam `#83C5BE`, Surface `#F7F9F8`).
3. Database menggunakan **MySQL (Laragon)** dengan koneksi `DB_CONNECTION=mysql` dan database `seasupply`.
4. Selalu jaga konsistensi peran (**Buyer**, **Owner**, **Employee**) dan middleware `store.role`.

---

## 1. Gambaran Peran Pengguna

| Peran | Tujuan | Area Akses Utama |
|---|---|---|
| **Buyer** | Belanja produk olahan laut lokal | `/catalog`, `/catalog/product/{id}`, `/catalog/orders` |
| **Owner** | Mengelola usaha, produk, dan pegawai | `/owner/dashboard`, `/owner/products`, `/owner/orders`, `/owner/finance`, `/owner/employees` |
| **Employee** | Membantu tugas operasional toko | `/employee/orders`, `/employee/summary`, `/employee/products` |

---

## 2. Struktur Database (MySQL `seasupply`)

- `users` (id, name, email, password)
- `stores` (id, name, description, location_label, is_active)
- `store_memberships` (id, store_id, user_id, role, status, permissions)
- `categories` (id, name, slug, icon, is_active)
- `products` (id, store_id, category_id, name, description, price, unit, stock_quantity, emoji, gradient, is_active, deleted_at)
- `orders` (id, order_code, store_id, buyer_user_id, status, subtotal, recipient_name, recipient_phone, delivery_address, buyer_note)
- `order_items` (id, order_id, product_id, product_name_snapshot, unit_price_snapshot, unit_snapshot, quantity, line_total)
- `sessions`, `cache`, `jobs`

---

## 3. Akun Demo Bawaan Seeder

- **Owner:** `hasan@seasupply.local` / `password123`
- **Employee:** `rian@seasupply.local` / `password123`
- **Buyer:** `budi@seasupply.local` / `password123`
