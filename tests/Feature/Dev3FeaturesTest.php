<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests untuk Modul Backend Minggu ke-2 (Lely)
 * Menguji: Cart, Payment Proof, Expense (FR-10), Offline Sale
 *
 * Jalankan: php artisan test --filter Dev3FeaturesTest
 */
class Dev3FeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $owner;
    private User $employee;
    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // ─── Seed users ───────────────────────────────────────────────────────
        $this->owner = User::factory()->create(['name' => 'Owner Tes', 'email' => 'owner@test.com']);
        $this->buyer = User::factory()->create(['name' => 'Pembeli Tes', 'email' => 'buyer@test.com']);
        $this->employee = User::factory()->create(['name' => 'Pegawai Tes', 'email' => 'emp@test.com']);

        // ─── Seed store & memberships ────────────────────────────────────────
        $this->store = Store::create([
            'name'           => 'Toko Laut Tes',
            'owner_user_id'  => $this->owner->id,
            'location_label' => 'Jakarta',
        ]);

        StoreMembership::create([
            'store_id'    => $this->store->id,
            'user_id'     => $this->owner->id,
            'role'        => 'owner',
            'status'      => 'active',
            'permissions' => [],
        ]);

        StoreMembership::create([
            'store_id'    => $this->store->id,
            'user_id'     => $this->employee->id,
            'role'        => 'employee',
            'status'      => 'active',
            'permissions' => ['orders.update_status', 'orders.read', 'products.read'],
        ]);

        // ─── Seed product ─────────────────────────────────────────────────────
        $this->product = Product::create([
            'store_id'       => $this->store->id,
            'category_id'    => null,
            'name'           => 'Rajungan Grade A',
            'description'    => 'Rajungan segar',
            'price'          => 85000,
            'unit'           => 'kg',
            'stock_quantity' => 50,
            'is_active'      => true,
            'emoji'          => '🦀',
            'gradient'       => 'from-orange-500 to-red-500',
        ]);
    }

    // =========================================================================
    // TEST 1: Cart – Add, Update, Remove, Get
    // =========================================================================

    public function test_cart_add_updates_session(): void
    {
        $response = $this->postJson('/cart/add', [
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.cart_count', 1);
    }

    public function test_cart_add_rejects_excessive_quantity(): void
    {
        $response = $this->postJson('/cart/add', [
            'product_id' => $this->product->id,
            'quantity'   => 999,
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');
    }

    public function test_cart_update_quantity(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->product->id, 'quantity' => 2]);

        $response = $this->patchJson("/cart/update/{$this->product->id}", ['quantity' => 5]);
        $response->assertOk();
    }

    public function test_cart_remove_item(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->product->id, 'quantity' => 2]);

        $response = $this->deleteJson("/cart/remove/{$this->product->id}");
        $response->assertOk()->assertJsonPath('data.cart_count', 0);
    }

    public function test_cart_get_returns_items_and_subtotal(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->product->id, 'quantity' => 2]);

        $response = $this->getJson('/cart/items');
        $response->assertOk()
                 ->assertJsonPath('data.count', 1)
                 ->assertJsonPath('data.subtotal', 170000); // 2 × 85000
    }

    // =========================================================================
    // TEST 2: Cart Checkout – Buat Order + Order Items
    // =========================================================================

    public function test_cart_checkout_creates_order_and_decrements_stock(): void
    {
        $this->actingAs($this->buyer);

        $this->postJson('/cart/add', ['product_id' => $this->product->id, 'quantity' => 5]);

        $response = $this->postJson('/cart/checkout', [
            'recipient_name'   => 'Budi Santoso',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Laut Biru No. 1',
            'buyer_note'       => 'Tolong kemas rapi',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['order_codes']]);

        // Cek stok berkurang
        $this->assertDatabaseHas('products', [
            'id'             => $this->product->id,
            'stock_quantity' => 45, // 50 - 5
        ]);

        // Cek order dibuat dengan transaction_type = online
        $this->assertDatabaseHas('orders', [
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'pending',
            'transaction_type' => 'online',
        ]);
    }

    public function test_cart_checkout_requires_auth(): void
    {
        $this->postJson('/cart/add', ['product_id' => $this->product->id, 'quantity' => 1]);

        $response = $this->postJson('/cart/checkout', [
            'recipient_name'   => 'Tanpa Login',
            'recipient_phone'  => '081111111111',
            'delivery_address' => 'Jl. Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_cart_checkout_fails_on_empty_cart(): void
    {
        $this->actingAs($this->buyer);

        $response = $this->postJson('/cart/checkout', [
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
        ]);

        $response->assertStatus(422)->assertJsonPath('error.code', 'CART_EMPTY');
    }

    // =========================================================================
    // TEST 3: Upload Bukti Pembayaran
    // =========================================================================

    public function test_payment_proof_upload_validates_and_stores(): void
    {
        Storage::fake('public');

        $this->actingAs($this->buyer);

        $order = Order::create([
            'order_code'       => 'SS-000001',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'pending',
            'subtotal'         => 85000,
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
            'transaction_type' => 'online',
        ]);

        $fakeFile = UploadedFile::fake()->image('bukti.jpg', 400, 400)->size(500);

        $response = $this->postJson("/catalog/orders/{$order->id}/upload-payment", [
            'proof_image' => $fakeFile,
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['data' => ['payment_proof_url']]);

        $order->refresh();
        $this->assertNotNull($order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    public function test_payment_proof_rejects_non_image(): void
    {
        $this->actingAs($this->buyer);

        $order = Order::create([
            'order_code'       => 'SS-000002',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'pending',
            'subtotal'         => 85000,
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
            'transaction_type' => 'online',
        ]);

        $fakeFile = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->postJson("/catalog/orders/{$order->id}/upload-payment", [
            'proof_image' => $fakeFile,
        ]);

        $response->assertStatus(422);
    }

    public function test_payment_proof_forbidden_for_other_buyer(): void
    {
        $this->actingAs($this->buyer);

        $otherBuyer = User::factory()->create();
        $order = Order::create([
            'order_code'       => 'SS-000003',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $otherBuyer->id, // milik orang lain
            'status'           => 'pending',
            'subtotal'         => 85000,
            'recipient_name'   => 'Orang Lain',
            'recipient_phone'  => '081999999999',
            'delivery_address' => 'Jl. Lain',
            'transaction_type' => 'online',
        ]);

        Storage::fake('public');
        $fakeFile = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->postJson("/catalog/orders/{$order->id}/upload-payment", [
            'proof_image' => $fakeFile,
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // TEST 4: Owner Verifikasi Pembayaran
    // =========================================================================

    public function test_owner_can_approve_payment(): void
    {
        $this->actingAs($this->owner);

        $order = Order::create([
            'order_code'       => 'SS-000010',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'pending',
            'subtotal'         => 85000,
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
            'payment_proof'    => 'payments/bukti.jpg',
            'transaction_type' => 'online',
        ]);

        $response = $this->patchJson("/owner/orders/{$order->order_code}/verify-payment", [
            'action' => 'approve',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('orders', ['order_code' => 'SS-000010', 'status' => 'processing']);
    }

    public function test_owner_can_reject_payment(): void
    {
        $this->actingAs($this->owner);

        $order = Order::create([
            'order_code'       => 'SS-000011',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'pending',
            'subtotal'         => 85000,
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
            'payment_proof'    => 'payments/bukti.jpg',
            'transaction_type' => 'online',
        ]);

        $response = $this->patchJson("/owner/orders/{$order->order_code}/verify-payment", [
            'action' => 'reject',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('orders', ['order_code' => 'SS-000011', 'payment_proof' => null]);
    }

    // =========================================================================
    // TEST 5: Expense (FR-10) CRUD + Net Cash
    // =========================================================================

    public function test_owner_can_create_expense(): void
    {
        $this->actingAs($this->owner);

        $response = $this->postJson('/owner/expenses', [
            'title'    => 'Beli Es Batu',
            'category' => 'es_batu',
            'amount'   => 150000,
            'date'     => now()->toDateString(),
            'notes'    => 'Untuk pengiriman pagi',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Beli Es Batu');

        $this->assertDatabaseHas('expenses', [
            'store_id' => $this->store->id,
            'title'    => 'Beli Es Batu',
            'amount'   => 150000,
            'category' => 'es_batu',
        ]);
    }

    public function test_expense_validates_category(): void
    {
        $this->actingAs($this->owner);

        $response = $this->postJson('/owner/expenses', [
            'title'    => 'Pengeluaran Tidak Valid',
            'category' => 'kategori_salah',
            'amount'   => 100000,
            'date'     => now()->toDateString(),
        ]);

        $response->assertStatus(422);
    }

    public function test_owner_can_delete_own_expense(): void
    {
        $this->actingAs($this->owner);

        $expense = Expense::create([
            'store_id'   => $this->store->id,
            'title'      => 'Solar Truk',
            'category'   => 'transport',
            'amount'     => 300000,
            'date'       => now()->toDateString(),
            'created_by' => $this->owner->id,
        ]);

        $response = $this->deleteJson("/owner/expenses/{$expense->id}");
        $response->assertOk();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_net_cash_calculation(): void
    {
        $this->actingAs($this->owner);

        // Buat pesanan selesai (income)
        Order::create([
            'order_code'       => 'SS-INC-001',
            'store_id'         => $this->store->id,
            'buyer_user_id'    => $this->buyer->id,
            'status'           => 'completed',
            'subtotal'         => 1000000,
            'recipient_name'   => 'Pembeli',
            'recipient_phone'  => '081234567890',
            'delivery_address' => 'Jl. Test',
            'transaction_type' => 'online',
        ]);

        // Buat pengeluaran
        Expense::create([
            'store_id'   => $this->store->id,
            'title'      => 'Beli Garam',
            'category'   => 'garam',
            'amount'     => 200000,
            'date'       => now()->toDateString(),
            'created_by' => $this->owner->id,
        ]);

        $response = $this->getJson('/owner/expenses');
        $response->assertOk()
                 ->assertJsonPath('data.net_cash', 800000); // 1.000.000 - 200.000
    }

    // =========================================================================
    // TEST 6: Offline Sale (Kasir Cepat)
    // =========================================================================

    public function test_offline_sale_creates_completed_order_with_type(): void
    {
        $this->actingAs($this->employee);

        $response = $this->postJson('/employee/orders/quick-sale', [
            'product_id'     => $this->product->id,
            'quantity'       => 2,
            'buyer_name'     => 'Pak Dedi',
            'buyer_phone'    => '081234567890',
            'payment_method' => 'Tunai',
            'cash_received'  => 200000,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'status'           => 'completed',
            'transaction_type' => 'offline_cash',
            'delivery_address' => 'Penjualan Langsung di Toko (Offline)',
        ]);

        $this->assertDatabaseHas('products', [
            'id'             => $this->product->id,
            'stock_quantity' => 48, // 50 - 2
        ]);
    }

    public function test_offline_sale_forbidden_without_permission(): void
    {
        // Buat pegawai tanpa izin
        $restrictedEmp = User::factory()->create();
        StoreMembership::create([
            'store_id'    => $this->store->id,
            'user_id'     => $restrictedEmp->id,
            'role'        => 'employee',
            'status'      => 'active',
            'permissions' => ['orders.read'], // tidak ada orders.update_status
        ]);

        $this->actingAs($restrictedEmp);

        $response = $this->postJson('/employee/orders/quick-sale', [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(403);
    }
}
