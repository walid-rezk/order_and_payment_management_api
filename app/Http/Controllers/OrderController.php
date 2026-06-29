<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with status filtering.
     */
    public function index(Request $request)
    {
        $query = Order::with(['items', 'payments'])
            ->where('user_id', auth()->id());

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'confirmed', 'cancelled'])) {
            $query->status($request->status);
        }

        $orders = $query->latest()->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): OrderResource
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {

            $order = Order::create([
                'user_id' => auth()->id(),
                'status'  => 'pending',
                'total'   => 0,
            ]);

            $total = 0;

            $itemsToInsert = collect($validated['items'])->map(function ($itemData) use (&$total) {
                $subtotal = round($itemData['quantity'] * $itemData['price'], 2);
                $total += $subtotal;

                return [
                    'product_name' => $itemData['product_name'],
                    'quantity'     => $itemData['quantity'],
                    'price'        => $itemData['price'],
                    'subtotal'     => $subtotal,
                ];
            })->all();

            $order->items()->createMany($itemsToInsert);

            $order->update(['total' => $total]);

            return $order;
        });

        return new OrderResource($order->load(['items', 'payments']));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): OrderResource|JsonResponse
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $order->load(['items', 'payments']);

        return new OrderResource($order);
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        if ($order->user_id !== auth()->id()) {
            abort(404, 'Order not found.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $order) {

            if (isset($validated['status'])) {
                $order->update(['status' => $validated['status']]);
            }

            if (isset($validated['items'])) {
                $order->items()->delete();

                $total = 0;
                $itemsToInsert = collect($validated['items'])->map(function ($itemData) use (&$total) {
                    $subtotal = round($itemData['quantity'] * $itemData['price'], 2);
                    $total += $subtotal;

                    return [
                        'product_name' => $itemData['product_name'],
                        'quantity'     => $itemData['quantity'],
                        'price'        => $itemData['price'],
                        'subtotal'     => $subtotal,
                    ];
                })->all();

                $order->items()->createMany($itemsToInsert);
                $order->update(['total' => $total]);
            }
        });

        return new OrderResource($order->load(['items', 'payments']));
    }

    /**
     * Remove the specified order.
     *
     * Orders with associated payments cannot be deleted.
     */
    public function destroy(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        // Check if order has payments
        if ($order->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete an order that has associated payments.',
            ], 422);
        }

        $order->items()->delete();
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }
}
