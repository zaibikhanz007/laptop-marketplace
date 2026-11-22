<?php

namespace Database\Factories;

use App\Models\Laptop;
use App\Models\LaptopImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaptopImage>
 */
class LaptopImageFactory extends Factory
{
    protected $model = LaptopImage::class;

    public function definition(): array
    {
        return [
            'laptop_id' => Laptop::factory(),
            'path' => 'laptops/'.fake()->uuid().'.jpg',
            'is_cover' => false,
        ];
    }

    public function cover(): static
    {
        return $this->state(['is_cover' => true]);
    }
}
