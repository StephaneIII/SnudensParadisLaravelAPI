<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // Get all orders belonging to a user
    public function index($UID)
    {
        $orders = Order::where('uid', $UID)
            ->with('orderItems')
            ->get();

        return response()->json(['orders' => $orders], 200);
    }

    // Create a new order with multiple products
    public function create(Request $request, $UID)
    {
        // Check that the authenticated user owns this UID
        if ((int) $request->user()->uid !== (int) $UID) {
            return response()->json([
                'message' => 'Unauthorized. You cannot create orders for another user.'
            ], 403);
        }

        // Validate the incoming request
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct',
            'items.*.amount' => 'required|integer|min:1',
        ]);

        // Create order and order items in a database transaction
        $order = DB::transaction(function () use ($validated, $UID) {

            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->all();

            // Retrieve products and lock them during the transaction
            $products = Product::whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            // Check that all requested products exist
            if ($products->count() !== count($productIds)) {
                throw ValidationException::withMessages([
                    'items' => ['One or more products do not exist.']
                ]);
            }

            // Calculate total price using database prices
            $totalInCents = 0;

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);

                $priceInCents = (int) round(
                    (float) $product->price * 100
                );

                $totalInCents += $priceInCents * $item['amount'];
            }

            $totalAmount = number_format(
                $totalInCents / 100,
                2,
                '.',
                ''
            );

            // Create the order
            $order = Order::create([
                'uid' => $UID,
                'order_date' => now(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Prepare order items
            $timestamp = now();

            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $orderItems[] = [
                    'order_id' => $order->order_id,
                    'product_id' => $item['product_id'],
                    'amount' => $item['amount'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            // Insert all order items
            DB::table('order_items')->insert($orderItems);

            return $order;
        });

        // Retrieve the saved order items
        $orderItems = DB::table('order_items')
            ->where('order_id', $order->order_id)
            ->get();

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
            'order_items' => $orderItems,
        ], 201);
    }

    // Add or update an item in an existing order
    public function addItem(Request $request, $orderId)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($orderId, $validated) {
            $order = Order::whereKey($orderId)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            $items = $order->orderItems()
                ->where('product_id', $validated['product_id']);

            $orderItem = $items->first();

            if ((int) $validated['quantity'] === 0) {
                if ($orderItem) {
                    $items->delete();
                }

                return response()->json([
                    'orderItem' => null
                ], 200);
            }

            if ($orderItem) {
                $items->update([
                    'amount' => $validated['quantity']
                ]);

                $orderItem->amount = $validated['quantity'];

                return response()->json([
                    'orderItem' => $orderItem
                ], 200);
            }

            $orderItem = $order->orderItems()->create([
                'product_id' => $validated['product_id'],
                'amount' => $validated['quantity'],
            ]);

            return response()->json([
                'orderItem' => $orderItem
            ], 201);
        });
    }
}
