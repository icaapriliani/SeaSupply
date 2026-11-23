<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreMembership;

class EmployeeController extends Controller
{
    private function getStore(): Store
    {
        return Store::firstOrCreate(['name' => 'Usaha Pak Hasan']);
    }

    private function checkEmployeePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Silakan masuk terlebih dahulu.');
        }

        $store = $this->getStore();
        $membership = StoreMembership::where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership || !$membership->hasPermission($permission)) {
            abort(403, "Akses Ditolak: Anda tidak memiliki izin '{$permission}'.");
        }
    }

    /**
     * GET /employee/orders (Wireframe D)
     */
    public function orders(Request $request)
    {
        $this->checkEmployeePermission('orders.read');

        $store  = $this->getStore();
        $status = $request->input('status', 'all');

        $query = Order::with(['items', 'buyer'])
            ->where('store_id', $store->id);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $allOrders = $query->latest()->get()->map(function ($o) {
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
            'pending'    => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'processing' => Order::where('store_id', $store->id)->where('status', 'processing')->count(),
            'shipped'    => Order::where('store_id', $store->id)->where('status', 'shipped')->count(),
            'completed'  => Order::where('store_id', $store->id)->where('status', 'completed')->count(),
            'cancelled'  => Order::where('store_id', $store->id)->where('status', 'cancelled')->count(),
        ];

        return view('employee.orders', [
            'orders' => $allOrders,
            'counts' => $counts,
        ]);
    }

    /**
     * PATCH /employee/orders/{orderCode}/status (Update Status Pesanan dengan Logika Pengembalian Stok)
     */
    public function updateOrderStatus(Request $request, $orderCode)
    {
        $user = Auth::user();
        $store = $this->getStore();
        $membership = StoreMembership::where('store_id', $store->id)
            ->where('user_id', $user?->id)
            ->where('status', 'active')
            ->first();

        if (!$membership || !$membership->hasPermission('orders.update_status')) {
            return response()->json([
                'error' => [
                    'code'    => 'FORBIDDEN',
                    'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk memperbarui status pesanan (orders.update_status).',
                ]
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $order = Order::with('items')->where('store_id', $store->id)->where('order_code', $orderCode)->firstOrFail();

        return DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            // Logika Pengembalian Stok pada Pembatalan:
            // Jika status pesanan diubah menjadi cancelled (Dibatalkan) oleh Pegawai, kembalikan kuantitas stok ke tabel products.
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

    /**
     * GET /employee/summary
     */
    public function summary()
    {
        $this->checkEmployeePermission('orders.read');

        $store = $this->getStore();

        $pendingCount    = Order::where('store_id', $store->id)->where('status', 'pending')->count();
        $processingCount = Order::where('store_id', $store->id)->where('status', 'processing')->count();
        $lowStockCount   = Product::where('store_id', $store->id)->where('stock_quantity', '<=', 10)->count();

        return view('employee.summary', compact('pendingCount', 'processingCount', 'lowStockCount'));
    }

    /**
     * GET /employee/products
     */
    public function products()
    {
        $this->checkEmployeePermission('products.read');

        $store = $this->getStore();

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($p) {
                return [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'category'  => $p->category?->name ?? 'Olahan Laut',
                    'price'     => $p->price,
                    'unit'      => $p->unit,
                    'stock'     => $p->stock_quantity,
                    'image_url' => $p->image_url,
                    'emoji'     => $p->emoji ?? '🐟',
                    'gradient'  => $p->gradient ?? 'linear-gradient(135deg,#D4E8F0,#A8D1E0)',
                ];
            });

        return view('employee.products', compact('products'));
    }
}
