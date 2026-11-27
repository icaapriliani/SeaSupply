<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    // ─── Helper: cart key ─────────────────────────────────────────────────────

    private string $sessionKey = 'cart';

    // ─── GET /cart/items ──────────────────────────────────────────────────────

    public function get()
    {
        $cart  = session($this->sessionKey, []);
        $items = array_values($cart);

        $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);

        return response()->json([
            'data'    => ['items' => $items, 'subtotal' => $subtotal, 'count' => count($items)],
            'message' => 'OK',
        ]);
    }

    // ─── POST /cart/add ───────────────────────────────────────────────────────

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:999',
        ]);

        $product = Product::where('id', $data['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $cart = session($this->sessionKey, []);
        $key  = (string) $product->id;

        $existingQty = isset($cart[$key]) ? $cart[$key]['qty'] : 0;
        $newQty      = $existingQty + $data['quantity'];

        if ($newQty > $product->stock_quantity) {
            return response()->json([
                'error' => [
                    'code'    => 'INSUFFICIENT_STOCK',
                    'message' => "Stok tidak mencukupi. Tersedia: {$product->stock_quantity} {$product->unit}.",
                ],
            ], 422);
        }

        $cart[$key] = [
            'id'    => $product->id,
            'name'  => $product->name,
            'price' => $product->price,
            'unit'  => $product->unit,
            'qty'   => $newQty,
            'stock' => $product->stock_quantity,
            'emoji' => $product->emoji ?? '🐟',
        ];

        session([$this->sessionKey => $cart]);

        return response()->json([
            'data'    => ['cart_count' => count($cart)],
            'message' => "{$product->name} berhasil ditambahkan ke keranjang.",
        ], 201);
    }

    // ─── PATCH /cart/update/{productId} ──────────────────────────────────────

    public function update(Request $request, int $productId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0|max:999',
        ]);

        $cart = session($this->sessionKey, []);
        $key  = (string) $productId;

        if (! isset($cart[$key])) {
            return response()->json(['error' => ['code' => 'NOT_IN_CART', 'message' => 'Produk tidak ditemukan di keranjang.']], 404);
        }

        if ($data['quantity'] === 0) {
            unset($cart[$key]);
            session([$this->sessionKey => $cart]);

            return response()->json(['data' => ['cart_count' => count($cart)], 'message' => 'Produk dihapus dari keranjang.']);
        }

        $product = Product::find($productId);
        if ($product && $data['quantity'] > $product->stock_quantity) {
            return response()->json([
                'error' => ['code' => 'INSUFFICIENT_STOCK', 'message' => "Stok tidak mencukupi. Tersedia: {$product->stock_quantity}."],
            ], 422);
        }

        $cart[$key]['qty'] = $data['quantity'];
        if ($product) {
            $cart[$key]['stock'] = $product->stock_quantity;
        }

        session([$this->sessionKey => $cart]);

        return response()->json(['data' => ['cart_count' => count($cart)], 'message' => 'Jumlah diperbarui.']);
    }

    // ─── DELETE /cart/remove/{productId} ─────────────────────────────────────

    public function remove(int $productId)
    {
        $cart = session($this->sessionKey, []);
        unset($cart[(string) $productId]);
        session([$this->sessionKey => $cart]);

        return response()->json(['data' => ['cart_count' => count($cart)], 'message' => 'Produk dihapus dari keranjang.']);
    }

    // ─── POST /cart/checkout ──────────────────────────────────────────────────

    public function checkout(Request $request)
    {
        $request->validate([
            'recipient_name'   => 'required|string|max:150',
            'recipient_phone'  => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
            'buyer_note'       => 'nullable|string|max:500',
        ]);

        $cart = session($this->sessionKey, []);

        if (empty($cart)) {
            return response()->json(['error' => ['code' => 'CART_EMPTY', 'message' => 'Keranjang belanja kosong.']], 422);
        }

        // Group cart items by store_id so we create one Order per store
        $productIds = array_keys($cart);
        $products   = Product::with('store')
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Validate all products still exist and are active
        foreach ($cart as $pid => $item) {
            if (! $products->has($pid)) {
                return response()->json(['error' => ['code' => 'PRODUCT_UNAVAILABLE', 'message' => "Produk \"{$item['name']}\" tidak tersedia."]], 422);
            }
        }

        // Group by store
        $byStore = [];
        foreach ($cart as $pid => $item) {
            $product   = $products->get($pid);
            $storeId   = $product->store_id;
            $byStore[$storeId][] = ['product' => $product, 'qty' => $item['qty']];
        }

        $orderCodes = [];

        try {
            DB::transaction(function () use ($byStore, $request, &$orderCodes) {
                foreach ($byStore as $storeId => $lines) {
                    $subtotal = 0;
                    $lineData = [];

                    foreach ($lines as $line) {
                        /** @var Product $product */
                        $product = Product::lockForUpdate()->find($line['product']->id);

                        if (! $product || ! $product->is_active) {
                            throw ValidationException::withMessages(['cart' => "Produk \"{$line['product']->name}\" tidak lagi tersedia."]);
                        }

                        if ($product->stock_quantity < $line['qty']) {
                            throw ValidationException::withMessages(['cart' => "Stok \"{$product->name}\" tidak mencukupi (tersisa {$product->stock_quantity} {$product->unit})."]);
                        }

                        $lineTotal  = $product->price * $line['qty'];
                        $subtotal  += $lineTotal;

                        $lineData[] = [
                            'product'              => $product,
                            'qty'                  => $line['qty'],
                            'line_total'           => $lineTotal,
                        ];

                        $product->decrement('stock_quantity', $line['qty']);
                    }

                    $order = Order::create([
                        'store_id'         => $storeId,
                        'buyer_user_id'    => Auth::id(),
                        'status'           => 'pending',
                        'subtotal'         => $subtotal,
                        'recipient_name'   => strip_tags($request->recipient_name),
                        'recipient_phone'  => strip_tags($request->recipient_phone),
                        'delivery_address' => strip_tags($request->delivery_address),
                        'buyer_note'       => $request->buyer_note ? strip_tags($request->buyer_note) : null,
                        'transaction_type' => 'online',
                    ]);

                    // Update order_code
                    $order->update(['order_code' => 'SS-' . str_pad($order->id, 6, '0', STR_PAD_LEFT)]);

                    foreach ($lineData as $ld) {
                        OrderItem::create([
                            'order_id'              => $order->id,
                            'product_id'            => $ld['product']->id,
                            'product_name_snapshot' => $ld['product']->name,
                            'unit_price_snapshot'   => $ld['product']->price,
                            'unit_snapshot'         => $ld['product']->unit,
                            'quantity'              => $ld['qty'],
                            'line_total'            => $ld['line_total'],
                        ]);
                    }

                    $orderCodes[] = $order->order_code;
                }
            });
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'CHECKOUT_FAILED', 'message' => collect($e->errors())->flatten()->first()]], 422);
        }

        // Clear cart
        session()->forget($this->sessionKey);

        return response()->json([
            'data'    => ['order_codes' => $orderCodes],
            'message' => 'Checkout berhasil! ' . count($orderCodes) . ' pesanan dibuat.',
        ], 201);
    }
}
