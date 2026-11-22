<?php

namespace Database\Factories;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laptop>
 */
class LaptopFactory extends Factory
{
    protected $model = Laptop::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'brand' => fake()->randomElement(['Dell', 'HP', 'Lenovo', 'Apple']),
            'model' => fake()->bothify('??-####'),
            'processor' => fake()->randomElement(['Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5', 'Apple M1']),
            'ram' => fake()->randomElement(['8 GB', '16 GB', '32 GB']),
            'storage' => fake()->randomElement(['256 GB SSD', '512 GB SSD', '1 TB SSD']),
            'condition' => fake()->randomElement(['like_new', 'good', 'fair', 'needs_repair']),
            'price' => fake()->numberBetween(20000, 300000),
            'location' => fake()->randomElement(['Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Peshawar', 'Faisalabad']),
            'description' => fake()->paragraph(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'rejection_reason' => null,
            'is_featured' => false,
            'featured_until' => null,
            'payment_account_title' => null,
            'payment_account_number' => null,
            'payment_bank_name' => null,
        ];
    }

    public function available(): static
    {
        return $this->state([
            'status' => 'available',
            'approval_status' => 'approved',
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);
    }

    public function sold(): static
    {
        return $this->state([
            'status' => 'sold',
            'approval_status' => 'approved',
        ]);
    }

    public function featured(): static
    {
        return $this->state([
            'status' => 'available',
            'approval_status' => 'approved',
            'is_featured' => true,
            'featured_until' => today()->addMonth(),
        ]);
    }
}
