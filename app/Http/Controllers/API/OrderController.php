<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // list all ordereditems of the user with UID
    public function index($UID)
    {
        $orderItems = Order::where('uid', $UID)->with('orderItems')->get()->pluck('orderItems')->flatten();

        return response()->json(['orderItems' => $orderItems], 200);
    }

    // create new order
    public function create(Request $request, $UID)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        $order = Order::create([
            'uid' => $UID,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return response()->json(['order' => $order], 201);
    }

    // add order item to order
    public function addItem(Request $request, $orderId)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($orderId, $validated) {
            $order = Order::whereKey($orderId)->lockForUpdate()->first();
            if (! $order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $items = $order->orderItems()->where('product_id', $validated['product_id']);
            $orderItem = $items->first();

            if ((int) $validated['quantity'] === 0) {
                if ($orderItem) {
                    $items->delete();
                }

                return response()->json(['orderItem' => null], 200);
            }

            if ($orderItem) {
                $items->update(['amount' => $validated['quantity']]);
                $orderItem->amount = $validated['quantity'];

                return response()->json(['orderItem' => $orderItem], 200);
            }

            $orderItem = $order->orderItems()->create([
                'product_id' => $validated['product_id'],
                'amount' => $validated['quantity'],
            ]);

            return response()->json(['orderItem' => $orderItem], 201);
        });
    }
}
