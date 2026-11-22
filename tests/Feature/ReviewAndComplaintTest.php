<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAndComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_cannot_review_before_order_is_completed(): void
    {
        [$buyer, $order] = $this->makeOrder('accepted');

        $this->actingAs($buyer)->post(route('reviews.store', $order), [
            'rating' => 5,
            'comment' => 'Great laptop.',
        ])->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_buyer_can_review_completed_order_only_once(): void
    {
        [$buyer, $order] = $this->makeOrder('completed');
        $payload = ['rating' => 5, 'comment' => 'Great laptop.'];

        $this->actingAs($buyer)->post(route('reviews.store', $order), $payload)->assertRedirect(route('orders.my'));
        $this->actingAs($buyer)->post(route('reviews.store', $order), $payload)->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    private function makeOrder(string $status): array
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = Laptop::factory()->available()->create(['user_id' => $seller->id]);
        $order = Order::factory()->create([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => $status,
        ]);

        return [$buyer, $order];
    }
}
