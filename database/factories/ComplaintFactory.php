<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        return [
            'complainant_id' => User::factory(),
            'against_user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'open',
            'admin_note' => null,
        ];
    }
}
