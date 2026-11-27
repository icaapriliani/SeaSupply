<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class Dev2FeaturesTest extends TestCase
{
    private Store $store;
    private User $ownerUser;
    private User $employeeUser;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan fake storage
        Storage::fake('public');

        // Setup store
        $this->store = Store::firstOrCreate(
            ['name' => 'Usaha Pak Hasan'],
            ['location_label' => 'Cirebon', 'is_active' => true]
        );

        // Setup Owner
        $this->ownerUser = User::firstOrCreate(
            ['email' => 'hasan@seasupply.local'],
            ['name' => 'Pak Hasan', 'password' => Hash::make('password123')]
        );
        StoreMembership::updateOrCreate(
            ['store_id' => $this->store->id, 'user_id' => $this->ownerUser->id],
            ['role' => 'owner', 'status' => 'active', 'permissions' => ['*']]
        );

        // Setup Employee
        $this->employeeUser = User::firstOrCreate(
            ['email' => 'rian@seasupply.local'],
            ['name' => 'Rian Hidayat', 'password' => Hash::make('password123')]
        );
        StoreMembership::updateOrCreate(
            ['store_id' => $this->store->id, 'user_id' => $this->employeeUser->id],
            ['role' => 'employee', 'status' => 'active', 'permissions' => ['orders.read', 'orders.update_status', 'products.read']]
        );

        // Setup Category
        $this->category = Category::firstOrCreate(
            ['slug' => 'rajungan'],
            ['name' => 'Rajungan', 'icon' => '🦀', 'is_active' => true]
        );
    }

    /**
     * Test 1: Upload Foto Produk Asli & Sanitasi XSS
     */
    public function test_owner_can_upload_product_image_and_xss_is_sanitized(): void
    {
        $fakeImage = UploadedFile::fake()->image('cumi-segar.jpg', 400, 400);

        $payload = [
            'name'        => '<script>alert("xss")</script>Cumi Asap Gurih',
            'category'    => 'Rajungan',
            'unit'        => '<b>kg</b>',
            'price'       => 75000,
            'stock'       => 20,
            'description' => '<img src=x onerror=alert(1)>Deskripsi cumi asap lezat',
            'image'       => $fakeImage,
        ];

        $response = $this->actingAs($this->ownerUser)
            ->postJson(route('owner.products.store'), $payload);

        $response->assertStatus(201);
        $productId = $response->json('data.id');

        $product = Product::findOrFail($productId);

        // Verifikasi nama dan deskripsi telah dibersihkan dari tag script/html XSS
        $this->assertStringNotContainsString('<script>', $product->name);
        $this->assertEquals('alert("xss")Cumi Asap Gurih', $product->name);
        $this->assertStringNotContainsString('<img', $product->description);
        $this->assertEquals('Deskripsi cumi asap lezat', $product->description);
        $this->assertEquals('kg', $product->unit);

        // Verifikasi file gambar tersimpan di storage/app/public/products
        $this->assertNotNull($product->image_url);
        Storage::disk('public')->assertExists($product->image_url);

        // Test Update Produk dengan foto baru
        $newFakeImage = UploadedFile::fake()->image('cumi-update.png', 400, 400);
        $oldImagePath = $product->image_url;

        $updateResponse = $this->actingAs($this->ownerUser)
            ->postJson(route('owner.products.update', $product->id), [
                '_method'     => 'PATCH',
                'name'        => 'Cumi Asap Super Gurih',
                'price'       => 80000,
                'stock'       => 25,
                'description' => 'Deskripsi baru',
                'image'       => $newFakeImage,
            ]);

        $updateResponse->assertStatus(200);
        $product->refresh();

        $this->assertEquals('Cumi Asap Super Gurih', $product->name);
        $this->assertNotEquals($oldImagePath, $product->image_url);
        Storage::disk('public')->assertExists($product->image_url);
        Storage::disk('public')->assertMissing($oldImagePath);
    }

    /**
     * Test 2: Kelola Pegawai & Hak Akses Penuh (/owner/employees)
     */
    public function test_owner_can_manage_employees_and_set_permissions(): void
    {
        $newEmail = 'staf_' . uniqid() . '@seasupply.local';

        $payload = [
            'name'        => '<script>alert(1)</script>Staf Baru',
            'email'       => $newEmail,
            'password'    => 'rahasia123',
            'permissions' => ['orders.read', 'products.read'],
        ];

        // 1. Tambah Pegawai Baru
        $response = $this->actingAs($this->ownerUser)
            ->postJson(route('owner.employees.store'), $payload);

        $response->assertStatus(201);
        $empId = $response->json('data.id');

        $membership = StoreMembership::with('user')->findOrFail($empId);
        $this->assertEquals('alert(1)Staf Baru', $membership->user->name);
        $this->assertTrue(Hash::check('rahasia123', $membership->user->password));
        $this->assertEquals(['orders.read', 'products.read'], $membership->permissions);
        $this->assertEquals('active', $membership->status);

        // 2. Update Hak Akses Pegawai
        $updateResponse = $this->actingAs($this->ownerUser)
            ->patchJson(route('owner.employees.update', $empId), [
                'status'      => 'active',
                'permissions' => ['orders.read', 'orders.update_status', 'products.read'],
            ]);

        $updateResponse->assertStatus(200);
        $membership->refresh();
        $this->assertEquals(['orders.read', 'orders.update_status', 'products.read'], $membership->permissions);

        // 3. Hapus Pegawai dari Toko
        $deleteResponse = $this->actingAs($this->ownerUser)
            ->deleteJson(route('owner.employees.destroy', $empId));

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('store_memberships', ['id' => $empId]);
    }

    /**
     * Test 3: Logika Pengembalian Stok pada Pembatalan Pesanan oleh Owner
     */
    public function test_order_cancellation_restores_product_stock_automatically(): void
    {
        $product = Product::create([
            'store_id'       => $this->store->id,
            'category_id'    => $this->category->id,
            'name'           => 'Rajungan Pedas Manis',
            'description'    => 'Rajungan segar',
            'price'          => 50000,
            'unit'           => 'kg',
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $orderCode = 'SS-TEST-' . uniqid();
        $order = Order::create([
            'order_code'      => $orderCode,
            'store_id'        => $this->store->id,
            'buyer_user_id'   => $this->ownerUser->id,
            'status'          => 'pending',
            'subtotal'        => 150000,
            'recipient_name'  => 'Pembeli Uji',
            'recipient_phone' => '08123456789',
        ]);

        OrderItem::create([
            'order_id'              => $order->id,
            'product_id'            => $product->id,
            'product_name_snapshot' => $product->name,
            'unit_price_snapshot'   => 50000,
            'unit_snapshot'         => 'kg',
            'quantity'              => 3,
            'line_total'            => 150000,
        ]);

        // Status awal: pending, stok = 10
        $this->assertEquals(10, $product->fresh()->stock_quantity);

        // Owner membatalkan pesanan (status -> cancelled)
        $cancelResponse = $this->actingAs($this->ownerUser)
            ->patchJson(route('owner.orders.update_status', $orderCode), [
                'status' => 'cancelled',
            ]);

        $cancelResponse->assertStatus(200);

        // Stok HARUS bertambah 3 unit menjadi 13 unit!
        $this->assertEquals(13, $product->fresh()->stock_quantity);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /**
     * Test 4: Pegawai Membatalkan Pesanan Mengembalikan Stok dan Pengecekan Izin RBAC
     */
    public function test_employee_cancellation_restores_stock_and_respects_permissions(): void
    {
        $product = Product::create([
            'store_id'       => $this->store->id,
            'category_id'    => $this->category->id,
            'name'           => 'Kerupuk Ikan Gurih',
            'description'    => 'Kerupuk gurih',
            'price'          => 20000,
            'unit'           => 'bungkus',
            'stock_quantity' => 15,
            'is_active'      => true,
        ]);

        $orderCode = 'SS-EMP-' . uniqid();
        $order = Order::create([
            'order_code'      => $orderCode,
            'store_id'        => $this->store->id,
            'buyer_user_id'   => $this->ownerUser->id,
            'status'          => 'processing',
            'subtotal'        => 100000,
            'recipient_name'  => 'Pembeli Rian',
            'recipient_phone' => '08123456789',
        ]);

        OrderItem::create([
            'order_id'              => $order->id,
            'product_id'            => $product->id,
            'product_name_snapshot' => $product->name,
            'unit_price_snapshot'   => 20000,
            'unit_snapshot'         => 'bungkus',
            'quantity'              => 5,
            'line_total'            => 100000,
        ]);

        // Pegawai membatalkan pesanan (status -> cancelled)
        $cancelResponse = $this->actingAs($this->employeeUser)
            ->patchJson(route('employee.orders.update_status', $orderCode), [
                'status' => 'cancelled',
            ]);

        $cancelResponse->assertStatus(200);

        // Stok HARUS bertambah 5 unit menjadi 20 unit!
        $this->assertEquals(20, $product->fresh()->stock_quantity);

        // Pegawai tanpa izin update_status tidak boleh ubah status
        $limitedUser = User::create([
            'name'     => 'Pegawai Tanpa Izin',
            'email'    => 'no_permission_' . uniqid() . '@seasupply.local',
            'password' => Hash::make('password123'),
        ]);
        StoreMembership::create([
            'store_id'    => $this->store->id,
            'user_id'     => $limitedUser->id,
            'role'        => 'employee',
            'status'      => 'active',
            'permissions' => ['orders.read'], // Tidak ada orders.update_status
        ]);

        $forbiddenResponse = $this->actingAs($limitedUser)
            ->patchJson(route('employee.orders.update_status', $orderCode), [
                'status' => 'processing',
            ]);

        $forbiddenResponse->assertStatus(403);
    }
}
