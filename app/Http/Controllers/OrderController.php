<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with optional status filtering.
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
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Create the order
        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'total' => 0,
        ]);

        // Create order items and calculate total
        $total = 0;
        foreach ($validated['items'] as $itemData) {
            $subtotal = round($itemData['quantity'] * $itemData['price'], 2);
            $total += $subtotal;

            $order->items()->create([
                'product_name' => $itemData['product_name'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'subtotal' => $subtotal,
            ]);
        }

        // Update order total
        $order->update(['total' => $total]);
        $order->load(['items', 'payments']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
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
    public function update(UpdateOrderRequest $request, Order $order): OrderResource|JsonResponse
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validated();

        // Update status if provided
        if (isset($validated['status'])) {
            $order->update(['status' => $validated['status']]);
        }

        // Update items if provided
        if (isset($validated['items'])) {
            // Delete existing items and replace
            $order->items()->delete();

            $total = 0;
            foreach ($validated['items'] as $itemData) {
                $subtotal = round($itemData['quantity'] * $itemData['price'], 2);
                $total += $subtotal;

                $order->items()->create([
                    'product_name' => $itemData['product_name'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total' => $total]);
        }

        $order->load(['items', 'payments']);

        return new OrderResource($order);
    }

    /**
     * Remove the specified order.
     *
     * Orders with associated payments cannot be deleted.
     */
    public function destroy(Order $order): JsonResponse
    {
        // Ensure the order belongs to the authenticated user
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
