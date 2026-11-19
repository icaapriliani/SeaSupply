<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $owner = User::firstOrCreate(
            ['email' => 'hasan@seasupply.local'],
            [
                'name'     => 'Pak Hasan',
                'password' => Hash::make('password123'),
            ]
        );

        $employee = User::firstOrCreate(
            ['email' => 'rian@seasupply.local'],
            [
                'name'     => 'Rian Hidayat',
                'password' => Hash::make('password123'),
            ]
        );

        $buyer = User::firstOrCreate(
            ['email' => 'budi@seasupply.local'],
            [
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Store
        $store = Store::firstOrCreate(
            ['name' => 'Usaha Pak Hasan'],
            [
                'description'    => 'UMKM Olahan Hasil Laut khas pesisir Cirebon berkualitas tinggi.',
                'location_label' => 'Cirebon, Jawa Barat',
                'is_active'      => true,
            ]
        );

        // 3. Store Memberships
        StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $owner->id],
            [
                'role'        => 'owner',
                'status'      => 'active',
                'permissions' => ['*'],
            ]
        );

        StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $employee->id],
            [
                'role'        => 'employee',
                'status'      => 'active',
                'permissions' => ['orders.read', 'orders.update_status', 'products.read'],
            ]
        );

        // 4. Categories
        $catRajungan = Category::firstOrCreate(['slug' => 'rajungan'], ['name' => 'Rajungan', 'icon' => '🦀', 'is_active' => true]);
        $catIkan     = Category::firstOrCreate(['slug' => 'ikan'], ['name' => 'Ikan Kering', 'icon' => '🐟', 'is_active' => true]);
        $catKerupuk  = Category::firstOrCreate(['slug' => 'kerupuk'], ['name' => 'Kerupuk Ikan', 'icon' => '🌾', 'is_active' => true]);
        $catUdang    = Category::firstOrCreate(['slug' => 'udang'], ['name' => 'Udang Olahan', 'icon' => '🦐', 'is_active' => true]);
        $catCumi     = Category::firstOrCreate(['slug' => 'cumi'], ['name' => 'Cumi-cumi', 'icon' => '🦑', 'is_active' => true]);
        $catKepiting = Category::firstOrCreate(['slug' => 'kepiting'], ['name' => 'Kepiting', 'icon' => '🦞', 'is_active' => true]);

        // 5. Products
        $p1 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Olahan Rajungan Premium'],
            [
                'category_id'    => $catRajungan->id,
                'description'    => 'Rajungan segar diolah dengan bumbu pilihan khas pesisir Cirebon, higienis, lezat, dan kaya cita rasa.',
                'price'          => 85000,
                'unit'           => 'kg',
                'stock_quantity' => 42,
                'emoji'          => '🦀',
                'gradient'       => 'linear-gradient(135deg,#FDE8D8,#F4C4A1)',
                'is_active'      => true,
            ]
        );

        $p2 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Ikan Garang Asap Pilihan'],
            [
                'category_id'    => $catIkan->id,
                'description'    => 'Ikan garang pilihan diasapi tradisional dengan kayu mangrove pilihan, menghasilkan cita rasa gurih alami tahan lama.',
                'price'          => 45000,
                'unit'           => 'kg',
                'stock_quantity' => 18,
                'emoji'          => '🐟',
                'gradient'       => 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                'is_active'      => true,
            ]
        );

        $p3 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Kerupuk Ikan Tenggiri'],
            [
                'category_id'    => $catKerupuk->id,
                'description'    => 'Kerupuk ikan tenggiri renyah, gurih tanpa bahan pengawet tambahan. Cocok untuk camilan keluarga.',
                'price'          => 28000,
                'unit'           => 'kg',
                'stock_quantity' => 150,
                'emoji'          => '🌾',
                'gradient'       => 'linear-gradient(135deg,#FDF4CD,#F5E49A)',
                'is_active'      => true,
            ]
        );

        $p4 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Udang Rebon Kering'],
            [
                'category_id'    => $catUdang->id,
                'description'    => 'Udang rebon segar dijemur alami di bawah terik matahari pesisir. Kaya protein dan mineral laut.',
                'price'          => 60000,
                'unit'           => 'kg',
                'stock_quantity' => 5,
                'emoji'          => '🦐',
                'gradient'       => 'linear-gradient(135deg,#FFE0D3,#FFB89A)',
                'is_active'      => true,
            ]
        );

        $p5 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Cumi Asin Spesial'],
            [
                'category_id'    => $catCumi->id,
                'description'    => 'Cumi-cumi asin berkualitas tinggi hasil tangkapan nelayan lokal, diolah dengan penggaraman higienis.',
                'price'          => 72000,
                'unit'           => 'kg',
                'stock_quantity' => 0, // Stok habis untuk pengujian
                'emoji'          => '🦑',
                'gradient'       => 'linear-gradient(135deg,#E8D5F5,#C9A8E8)',
                'is_active'      => true,
            ]
        );

        $p6 = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Kepiting Bakau Olahan'],
            [
                'category_id'    => $catKepiting->id,
                'description'    => 'Kepiting bakau segar diolah dengan bumbu rempah pilihan. Daging tebal dan manis.',
                'price'          => 110000,
                'unit'           => 'kg',
                'stock_quantity' => 8,
                'emoji'          => '🦞',
                'gradient'       => 'linear-gradient(135deg,#D5EFD5,#9ED69E)',
                'is_active'      => true,
            ]
        );

        // 6. Orders & Order Items
        $this->seedOrder($store, $buyer, 'SS-001', 'pending', $p1, 2, 'Budi Santoso', '0812-3456-7890');
        $this->seedOrder($store, $buyer, 'SS-002', 'processing', $p2, 3, 'Siti Aminah', '0857-1234-5678');
        $this->seedOrder($store, $buyer, 'SS-003', 'shipped', $p3, 5, 'Ahmad Fadil', '0878-9988-7766');
        $this->seedOrder($store, $buyer, 'SS-004', 'completed', $p6, 1, 'Dewi Lestari', '0813-4455-6677');
    }

    private function seedOrder(Store $store, User $buyer, string $code, string $status, Product $product, int $qty, string $recipientName, string $phone): void
    {
        $subtotal = $product->price * $qty;

        $order = Order::firstOrCreate(
            ['order_code' => $code],
            [
                'store_id'        => $store->id,
                'buyer_user_id'   => $buyer->id,
                'status'          => $status,
                'subtotal'        => $subtotal,
                'recipient_name'  => $recipientName,
                'recipient_phone' => $phone,
                'delivery_address'=> 'Jl. Bahari No. 12, Pesisir Cirebon',
                'buyer_note'      => 'Mohon dipacking dengan bubble wrap aman.',
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'product_name_snapshot' => $product->name,
                'unit_price_snapshot'   => $product->price,
                'unit_snapshot'         => $product->unit,
                'quantity'              => $qty,
                'line_total'            => $subtotal,
            ]
        );
    }
}
