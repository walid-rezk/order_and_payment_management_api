<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use InvalidArgumentException;
use App\Services\PaymentManager;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\ProcessPaymentRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentManager $paymentManager,
    ) {
    }

    /**
     * Display all payments for the authenticated user.
     */
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::whereHas('order', function ($query) {
            $query->where('user_id', auth()->id());
        })
            ->with('order')
            ->latest()
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    /**
     * Display payments for a specific order.
     */
    public function orderPayments(Order $order): AnonymousResourceCollection|JsonResponse
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $payments = $order->payments()->latest()->paginate(15);

        return PaymentResource::collection($payments);
    }

    /**
     * Process a payment for the given order.
     *
     * payments can only be processed for orders in 'confirmed' status.
     */
    public function processPayment(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        // only confirmed orders can be paid
        if ($order->status !== OrderStatus::CONFIRMED) {
            return response()->json([
                'message' => 'Payments can only be processed for orders in confirmed status.',
                'current_status' => $order->status,
            ], 422);
        }

        $validated = $request->validated();

        // Resolve the appropriate gateway
        try {
            $gateway = $this->paymentManager->resolveGateway($validated['payment_method']);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Create a pending payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'status' => PaymentStatus::PENDING,
        ]);

        // Process through the gateway
        $result = $gateway->processPayment($order, (float) $validated['amount']);

        // Update payment based on result
        $payment->update([
            'status' => $result->success ? PaymentStatus::SUCCESSFUL : PaymentStatus::FAILED,
            'gateway_transaction_id' => $result->transactionId ?: null,
        ]);

        $statusCode = $result->success ? 201 : 422;

        return response()->json([
            'message' => $result->message,
            'payment' => new PaymentResource($payment),
        ], $statusCode);
    }
}
