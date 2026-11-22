<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTrustTest extends TestCase
{
    use RefreshDatabase;

    private function makeLaptop(User $seller): Laptop
    {
        return Laptop::forceCreate([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude',
            'processor' => 'Intel Core i7',
            'ram' => '16 GB',
            'storage' => '512 GB SSD',
            'condition' => 'like_new',
            'price' => 100000,
            'location' => 'Lahore',
            'description' => 'Test laptop',
            'status' => 'available',
            'approval_status' => 'approved',
        ]);
    }

    private function makeOrder(User $seller, User $buyer, string $status = 'completed'): Order
    {
        return Order::forceCreate([
            'laptop_id' => $this->makeLaptop($seller)->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Test address',
            'payment_method' => 'cod',
            'status' => $status,
        ]);
    }

    public function test_buyer_cannot_review_non_completed_order(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $order = $this->makeOrder($seller, $buyer, 'accepted');

        $this->actingAs($buyer)
            ->get(route('reviews.create', $order))
            ->assertForbidden();
    }

    public function test_non_buyer_cannot_review_order(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->makeOrder($seller, $buyer);

        $this->actingAs($other)
            ->post(route('reviews.store', $order), ['rating' => 5, 'comment' => 'Test'])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_duplicate_review_is_blocked(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $order = $this->makeOrder($seller, $buyer);

        Review::forceCreate([
            'order_id' => $order->id,
            'reviewer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'rating' => 4,
            'comment' => 'Already reviewed',
        ]);

        $this->actingAs($buyer)
            ->post(route('reviews.store', $order), ['rating' => 5, 'comment' => 'Duplicate'])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_rating_out_of_range_is_rejected(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $order = $this->makeOrder($seller, $buyer);

        $this->actingAs($buyer)
            ->from(route('reviews.create', $order))
            ->post(route('reviews.store', $order), ['rating' => 6, 'comment' => 'Too high'])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_user_cannot_complain_against_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('complaints.store', $user), [
                'subject' => 'Test',
                'description' => 'Cannot report myself',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_fourth_open_complaint_against_same_user_is_blocked(): void
    {
        $complainant = User::factory()->create();
        $against = User::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            Complaint::forceCreate([
                'complainant_id' => $complainant->id,
                'against_user_id' => $against->id,
                'subject' => "Complaint {$i}",
                'description' => 'Open complaint',
                'status' => 'open',
            ]);
        }

        $this->actingAs($complainant)
            ->post(route('complaints.store', $against), [
                'subject' => 'Fourth complaint',
                'description' => 'This should be blocked',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('complaints', 3);
    }

    public function test_seller_average_rating_and_review_count_are_correct(): void
    {
        $seller = User::factory()->create();
        $buyerOne = User::factory()->create();
        $buyerTwo = User::factory()->create();

        foreach ([$buyerOne, $buyerTwo] as $index => $buyer) {
            $order = $this->makeOrder($seller, $buyer);
            Review::forceCreate([
                'order_id' => $order->id,
                'reviewer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'rating' => $index === 0 ? 4 : 5,
                'comment' => 'Rating test',
            ]);
        }

        $seller->refresh();

        $this->assertSame(4.5, $seller->averageRating());
        $this->assertSame(2, $seller->reviewCount());

        $this->get(route('sellers.show', $seller))
            ->assertOk()
            ->assertSee('4.5')
            ->assertSee('(2)');
    }
}
