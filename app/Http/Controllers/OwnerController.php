<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\StoreMembership;
use App\Models\User;

class OwnerController extends Controller
{
    private function getOwnerStore(): Store
    {
        return Store::firstOrCreate(
            ['name' => 'Usaha Pak Hasan'],
            [
                'location_label' => 'Cirebon, Jawa Barat',
                'is_active'      => true,
            ]
        );
    }

    /**
     * GET /owner/dashboard (Wireframe C)
     */
    public function dashboard()
    {
        $store = $this->getOwnerStore();

        $activeProductsCount = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->count();

        $incomingOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $totalSales = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');

        $stats = [
            'active_products_count' => $activeProductsCount,
            'incoming_orders_count' => $incomingOrdersCount,
            'total_sales'           => (int) $totalSales,
        ];

        $orders = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id)
            ->latest()
            ->take(8)
            ->get();

        $recentOrders = $orders->map(function ($o) {
            $firstItem = $o->items->first();
            return [
                'id'           => $o->id,
                'order_code'   => $o->order_code,
                'buyer_name'   => $o->recipient_name ?? ($o->buyer?->name ?? 'Pembeli'),
                'buyer_phone'  => $o->recipient_phone ?? '0812-3456-7890',
                'item_name'    => $firstItem?->product_name_snapshot ?? 'Produk Olahan Laut',
                'quantity'     => $firstItem?->quantity ?? 1,
                'unit'         => $firstItem?->unit_snapshot ?? 'kg',
                'total_amount' => $o->subtotal,
                'status'       => $o->status,
                'date'         => $o->created_at->format('d M Y, H:i'),
            ];
        });

        return view('owner.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * GET /owner/products (Daftar Produk Toko Lengkap)
     */
    public function products()
    {
        $store = $this->getOwnerStore();

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'category'    => $p->category?->name ?? 'Olahan Laut',
                    'price'       => $p->price,
                    'unit'        => $p->unit,
                    'stock'       => $p->stock_quantity,
                    'is_active'   => $p->is_active,
                    'image_url'   => $p->image_url,
                    'emoji'       => $p->emoji ?? '🐟',
                    'gradient'    => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                    'description' => $p->description,
                ];
            });

        return view('owner.products', compact('products'));
    }

    /**
     * POST /owner/products (Tambah Produk Baru ke Database dengan Upload Foto & Sanitasi XSS)
     */
    public function storeProduct(Request $request)
    {
        $store = $this->getOwnerStore();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'unit'        => 'required|string|max:20',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Sanitasi input form produk untuk mencegah celah keamanan XSS
        $cleanName        = strip_tags(trim($validated['name']));
        $cleanUnit        = strip_tags(trim($validated['unit']));
        $cleanDescription = isset($validated['description']) ? strip_tags(trim($validated['description'])) : '';

        $category = Category::where('name', $validated['category'])->first()
            ?? Category::first();

        $emojiMap = [
            'Rajungan'     => '🦀',
            'Ikan Kering'  => '🐟',
            'Kerupuk Ikan' => '🌾',
            'Udang Olahan' => '🦐',
            'Cumi-cumi'    => '🦑',
            'Kepiting'     => '🦞',
        ];

        // Logic penyimpanan file foto produk ke storage/app/public/products
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'store_id'       => $store->id,
            'category_id'    => $category->id,
            'name'           => $cleanName,
            'description'    => $cleanDescription,
            'price'          => $validated['price'],
            'unit'           => $cleanUnit ?: 'kg',
            'stock_quantity' => $validated['stock'],
            'image_url'      => $imagePath,
            'emoji'          => $emojiMap[$validated['category']] ?? '🐟',
            'gradient'       => 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
            'is_active'      => true,
        ]);

        return response()->json([
            'data'    => $product,
            'message' => "Produk '{$product->name}' berhasil ditambahkan ke database.",
        ], 201);
    }

    /**
     * PATCH/POST /owner/products/{id} (Edit Produk dengan Upload Foto Baru & Sanitasi XSS)
     */
    public function updateProduct(Request $request, $id)
    {
        $store = $this->getOwnerStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Sanitasi input teks untuk mencegah celah keamanan XSS
        $cleanName        = strip_tags(trim($validated['name']));
        $cleanDescription = isset($validated['description']) ? strip_tags(trim($validated['description'])) : '';

        $updateData = [
            'name'           => $cleanName,
            'price'          => $validated['price'],
            'stock_quantity' => $validated['stock'],
            'description'    => $cleanDescription,
        ];

        // Jika ada unggahan gambar baru, simpan file baru dan hapus file lama
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('products', 'public');

            if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                Storage::disk('public')->delete($product->image_url);
            }

            $updateData['image_url'] = $newImagePath;
        }

        $product->update($updateData);

        return response()->json([
            'data'    => $product,
            'message' => "Produk '{$product->name}' berhasil diperbarui.",
        ]);
    }

    /**
     * DELETE /owner/products/{id} (Soft Delete / Arsipkan Produk Sesuai Design System)
     */
    public function destroyProduct($id)
    {
        $store = $this->getOwnerStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $name = $product->name;
        $product->delete(); // Soft delete

        return response()->json([
            'message' => "Produk '{$name}' berhasil dihapus dari katalog.",
        ]);
    }

    /**
     * GET /owner/orders (Daftar Pesanan Toko Lengkap)
     */
    public function orders(Request $request)
    {
        $store  = $this->getOwnerStore();
        $status = $request->input('status', 'all');

        $allOrders = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id)
            ->latest()
            ->get()
            ->map(function ($o) {
                $firstItem = $o->items->first();
                return [
                    'id'           => $o->id,
                    'order_code'   => $o->order_code,
                    'buyer_name'   => $o->recipient_name ?? ($o->buyer?->name ?? 'Pembeli'),
                    'buyer_phone'  => $o->recipient_phone ?? '0812-3456-7890',
                    'item_name'    => $firstItem?->product_name_snapshot ?? 'Produk Olahan Laut',
                    'quantity'     => $firstItem?->quantity ?? 1,
                    'unit'         => $firstItem?->unit_snapshot ?? 'kg',
                    'total_amount' => $o->subtotal,
                    'status'       => $o->status,
                    'date'         => $o->created_at->format('d M Y, H:i'),
                ];
            });

        $counts = [
            'pending'    => $allOrders->where('status', 'pending')->count(),
            'processing' => $allOrders->where('status', 'processing')->count(),
            'shipped'    => $allOrders->where('status', 'shipped')->count(),
            'completed'  => $allOrders->where('status', 'completed')->count(),
        ];

        if ($status && $status !== 'all') {
            $filteredOrders = $allOrders->filter(fn($o) => $o['status'] === $status)->values();
        } else {
            $filteredOrders = $allOrders;
        }

        return view('owner.orders', [
            'orders'    => $filteredOrders,
            'allOrders' => $allOrders,
            'counts'    => $counts,
        ]);
    }

    /**
     * PATCH /owner/orders/{orderCode}/status (Update Status Pesanan dengan Logika Pengembalian Stok)
     */
    public function updateOrderStatus(Request $request, $orderCode)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $store = $this->getOwnerStore();
        $order = Order::with('items')->where('store_id', $store->id)->where('order_code', $orderCode)->firstOrFail();

        return DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            // Logika Pengembalian Stok pada Pembatalan:
            // Jika status pesanan diubah menjadi cancelled (Dibatalkan), kembalikan kuantitas stok ke tabel products.
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $product->stock_quantity += $item->quantity;
                            $product->save();
                        }
                    }
                }
            }
            // Jika pesanan yang tadinya cancelled diaktifkan kembali
            elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                // Periksa apakah stok mencukupi
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                        if ($product && $product->stock_quantity < $item->quantity) {
                            return response()->json([
                                'error' => [
                                    'code'    => 'INSUFFICIENT_STOCK',
                                    'message' => "Stok produk '{$product->name}' tidak mencukupi untuk mengaktifkan kembali pesanan.",
                                ]
                            ], 400);
                        }
                    }
                }

                // Kurangi stok kembali
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $product->stock_quantity = max(0, $product->stock_quantity - $item->quantity);
                            $product->save();
                        }
                    }
                }
            }

            $order->status = $newStatus;
            $order->save();

            $msg = "Status pesanan #{$order->order_code} berhasil diperbarui menjadi {$order->status}.";
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                $msg .= " Kuantitas stok produk telah dikembalikan otomatis ke katalog.";
            }

            return response()->json([
                'data'    => $order,
                'message' => $msg,
            ]);
        });
    }

    public function finance(Request $request)
    {
        $store = $this->getOwnerStore();
        $period = $request->input('period', 'all');

        $query = Order::with('items')->where('store_id', $store->id);

        $now = now();
        $periodLabel = 'Sepanjang Waktu';

        if ($period === 'today') {
            $query->whereDate('created_at', $now->toDateString());
            $periodLabel = 'Hari Ini (' . $now->format('d M Y') . ')';
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', $now->copy()->subDays(7));
            $periodLabel = '7 Hari Terakhir';
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', $now->copy()->startOfMonth());
            $periodLabel = 'Bulan Ini (' . $now->format('M Y') . ')';
        }

        $orders = $query->get();

        $allOrdersCount = $orders->count();
        $paidOrders = $orders->where('status', '!=', 'cancelled');
        $paidOrdersCount = $paidOrders->count();
        $totalSales = $paidOrders->sum('subtotal');
        $aov = $paidOrdersCount > 0 ? (int) ($totalSales / $paidOrdersCount) : 0;

        // Hitung total berat/kuantitas & breakdown per produk
        $productStats = [];
        $totalWeight = 0;

        foreach ($paidOrders as $order) {
            foreach ($order->items as $item) {
                $pName = $item->product_name_snapshot;
                $totalWeight += $item->quantity;

                if (!isset($productStats[$pName])) {
                    $productStats[$pName] = [
                        'name'          => $pName,
                        'price'         => $item->unit_price_snapshot,
                        'unit'          => $item->unit_snapshot,
                        'total_qty'     => 0,
                        'total_revenue' => 0,
                    ];
                }

                $productStats[$pName]['total_qty'] += $item->quantity;
                $productStats[$pName]['total_revenue'] += $item->line_total;
            }
        }

        // Urutkan produk terlaris berdasarkan revenue tertinggi
        usort($productStats, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        $metrics = [
            'total_sales'         => (int) $totalSales,
            'paid_orders_count'   => $paidOrdersCount,
            'all_orders_count'    => $allOrdersCount,
            'total_weight_kg'     => $totalWeight,
            'average_order_value' => $aov,
        ];

        return view('owner.finance', [
            'metrics'     => $metrics,
            'topProducts' => array_values($productStats),
            'periodLabel' => $periodLabel,
            'period'      => $period,
        ]);
    }

    public function employees()
    {
        $store = $this->getOwnerStore();

        $memberships = StoreMembership::with('user')
            ->where('store_id', $store->id)
            ->where('role', 'employee')
            ->latest()
            ->get();

        $employees = $memberships->map(function ($m) {
            return [
                'id'          => $m->id,
                'user_id'     => $m->user_id,
                'name'        => $m->user?->name ?? 'Pegawai',
                'email'       => $m->user?->email ?? '-',
                'role'        => 'Staf Operasional',
                'permissions' => $m->permissions ?? ['orders.read'],
                'status'      => $m->status,
                'created_at'  => $m->created_at?->format('d M Y') ?? '-',
            ];
        });

        return view('owner.employees', compact('employees'));
    }

    /**
     * POST /owner/employees (Owner Menambah Pegawai Baru)
     */
    public function storeEmployee(Request $request)
    {
        $store = $this->getOwnerStore();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:orders.read,orders.update_status,products.read',
        ]);

        $cleanName  = strip_tags(trim($validated['name']));
        $cleanEmail = trim(strtolower($validated['email']));

        $user = User::create([
            'name'     => $cleanName,
            'email'    => $cleanEmail,
            'password' => Hash::make($validated['password']),
        ]);

        $permissions = $validated['permissions'] ?? ['orders.read'];
        if (empty($permissions)) {
            $permissions = ['orders.read'];
        }

        $membership = StoreMembership::create([
            'store_id'    => $store->id,
            'user_id'     => $user->id,
            'role'        => 'employee',
            'status'      => 'active',
            'permissions' => array_values($permissions),
        ]);

        return response()->json([
            'data' => [
                'id'          => $membership->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => 'Staf Operasional',
                'permissions' => $membership->permissions,
                'status'      => $membership->status,
            ],
            'message' => "Pegawai '{$user->name}' berhasil ditambahkan ke toko.",
        ], 201);
    }

    /**
     * PATCH /owner/employees/{id} (Update Hak Akses & Status Pegawai)
     */
    public function updateEmployee(Request $request, $id)
    {
        $store = $this->getOwnerStore();
        $membership = StoreMembership::with('user')
            ->where('store_id', $store->id)
            ->where('role', 'employee')
            ->findOrFail($id);

        $validated = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:orders.read,orders.update_status,products.read',
            'status'        => 'nullable|in:active,inactive',
        ]);

        if (isset($validated['permissions'])) {
            $membership->permissions = array_values($validated['permissions']);
        }

        if (isset($validated['status'])) {
            $membership->status = $validated['status'];
        }

        $membership->save();

        return response()->json([
            'data'    => $membership,
            'message' => "Hak akses untuk pegawai '{$membership->user?->name}' berhasil diperbarui.",
        ]);
    }

    /**
     * DELETE /owner/employees/{id} (Hapus Pegawai dari Toko)
     */
    public function destroyEmployee($id)
    {
        $store = $this->getOwnerStore();
        $membership = StoreMembership::with('user')
            ->where('store_id', $store->id)
            ->where('role', 'employee')
            ->findOrFail($id);

        $employeeName = $membership->user?->name ?? 'Pegawai';
        $membership->delete();

        return response()->json([
            'message' => "Pegawai '{$employeeName}' berhasil dihapus dari daftar pegawai toko.",
        ]);
    }
}
