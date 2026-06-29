<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => 'pending',
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'gateway_transaction_id' => null,
            'amount' => $this->faker->randomFloat(2, 10, 500),
        ];
    }

    /**
     * Set the payment as successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'successful',
            'gateway_transaction_id' => 'txn_' . $this->faker->uuid(),
        ]);
    }

    /**
     * Set the payment as failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
