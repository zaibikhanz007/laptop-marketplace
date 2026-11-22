<?php

namespace Database\Factories;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Order $order): void {
            $order->seller_id = Laptop::query()->whereKey($order->laptop_id)->value('user_id');
        });
    }

    public function definition(): array
    {
        return [
            'laptop_id' => Laptop::factory()->available(),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'phone' => '03'.fake()->numerify('#########'),
            'address' => fake()->address(),
            'payment_method' => fake()->randomElement(['cod', 'bank_transfer']),
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function accepted(): static
    {
        return $this->state(['status' => 'accepted']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
