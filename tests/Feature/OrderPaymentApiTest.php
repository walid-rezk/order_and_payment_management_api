<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a user
        $this->user = User::factory()->create([
            'email' => 'walid@test.com',
            'password' => bcrypt('12345678')
        ]);

        // 2. Mock a login request to capture the explicit token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'walid@test.com',
            'password' => '12345678'
        ]);

        $this->token = $response->json('authorization.token');
    }

    public function test_unauthenticated_requests_are_blocked()
    {
        auth()->logout();
        $this->getJson('/api/orders')->assertStatus(401);
    }

    public function test_it_can_create_an_order_with_items_and_calculated_total()
    {
        $orderData = [
            'customer_name' => 'Walid',
            'customer_email' => 'walid@test.com',
            'items' => [
                ['product_name' => 'Wireless Mouse', 'quantity' => 2, 'price' => 25.00],
                ['product_name' => 'Mechanical Keyboard', 'quantity' => 1, 'price' => 80.00]
            ]
        ];

        $this->postJson('/api/orders', $orderData, ['Authorization' => "Bearer $this->token"])
            ->assertStatus(201)
            ->assertJsonPath('data.total', '130.00')
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_a_user_cannot_view_another_users_order()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $this->getJson("/api/orders/{$order->id}", ['Authorization' => "Bearer $this->token"])
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'Order not found.']);
    }

    public function test_it_cannot_process_payment_for_an_order_unless_it_is_confirmed()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
            'total' => 90.00
        ]);

        $this->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'credit_card',
        ], ['Authorization' => "Bearer $this->token"])
            ->assertStatus(422);
    }

    public function test_it_successfully_processes_payment_for_confirmed_orders()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status'  => OrderStatus::CONFIRMED,
            'total'   => 90.00
        ]);

        $mockGateway = Mockery::mock(\App\Services\PaymentGateways\CreditCardGateway::class);
        $mockGateway->shouldReceive('processPayment')
            ->once()
            ->with(Mockery::any(), 90.00)
            ->andReturn(new \App\DTOs\PaymentResult(
                success: true,
                message: 'successful',
                transactionId: 'tx_mock_777'
            ));

        $mockPaymentManager = Mockery::mock(\App\Services\PaymentManager::class);

        $mockPaymentManager->shouldReceive('getAvailableGateways')
            ->zeroOrMoreTimes()
            ->andReturn(['credit_card', 'paypal']);

        $mockPaymentManager->shouldReceive('resolveGateway')
            ->once()
            ->with('credit_card')
            ->andReturn($mockGateway);

        $this->instance(\App\Services\PaymentManager::class, $mockPaymentManager);

        $this->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'credit_card',
            'amount' => 90.00
        ], ['Authorization' => "Bearer $this->token"])
            ->assertStatus(201)
            ->assertJsonPath('payment.status', 'successful');
    }

    public function test_it_cannot_delete_an_order_if_it_has_associated_payments()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        Payment::factory()->create(['order_id' => $order->id]);

        $this->deleteJson("/api/orders/{$order->id}", [], ['Authorization' => "Bearer $this->token"])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot delete an order that has associated payments.']);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_it_can_delete_an_order_without_associated_payments()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/orders/{$order->id}", [], ['Authorization' => "Bearer $this->token"])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Order deleted successfully.']);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
