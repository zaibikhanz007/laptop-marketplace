<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Review $review): void {
            $order = Order::query()->find($review->order_id);
            $review->reviewer_id = $order?->buyer_id ?? $review->reviewer_id;
            $review->seller_id = $order?->seller_id ?? $review->seller_id;
        });
    }

    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->completed(),
            'reviewer_id' => User::factory(),
            'seller_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
