<?php

namespace Tests\Unit;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_identifies_admin_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($seller->isAdmin());
    }

    public function test_rating_helpers_return_average_and_review_count(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = Laptop::factory()->available()->create(['user_id' => $seller->id]);
        $orders = Order::factory()->count(3)->completed()->create([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        Review::factory()->create(['order_id' => $orders[0]->id, 'seller_id' => $seller->id, 'rating' => 4]);
        Review::factory()->create(['order_id' => $orders[1]->id, 'seller_id' => $seller->id, 'rating' => 4]);
        Review::factory()->create(['order_id' => $orders[2]->id, 'seller_id' => $seller->id, 'rating' => 5]);

        $this->assertSame(4.3, $seller->averageRating());
        $this->assertSame(3, $seller->reviewCount());
    }
}
