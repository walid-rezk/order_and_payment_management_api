<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 5, 100);

        return [
            'order_id' => Order::factory(),
            'product_name' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => round($quantity * $price, 2),
        ];
    }
}
