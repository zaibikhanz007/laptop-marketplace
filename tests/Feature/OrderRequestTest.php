<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeLaptop(User $seller, array $attributes = []): Laptop
    {
        return Laptop::forceCreate(array_merge([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude 7420',
            'processor' => 'Intel Core i7',
            'ram' => '16 GB',
            'storage' => '512 GB SSD',
            'condition' => 'like_new',
            'price' => 145000,
            'location' => 'Karachi',
            'description' => 'Test listing',
            'status' => 'available',
            'approval_status' => 'approved',
        ], $attributes));
    }

    public function test_user_cannot_buy_their_own_laptop(): void
    {
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $response = $this->actingAs($seller)->post(route('orders.store', $laptop), [
            'phone' => '03001234567',
            'address' => 'Test address',
            'payment_method' => 'cod',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_duplicate_pending_or_accepted_request_is_blocked(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        foreach (['pending', 'accepted'] as $status) {
            Order::where('laptop_id', $laptop->id)
                ->where('buyer_id', $buyer->id)
                ->delete();

            Order::forceCreate([
                'laptop_id' => $laptop->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'phone' => '03001234567',
                'address' => 'Existing address',
                'payment_method' => 'cod',
                'status' => $status,
            ]);

            $response = $this->actingAs($buyer)->post(route('orders.store', $laptop), [
                'phone' => '03112223334',
                'address' => 'Duplicate address',
                'payment_method' => 'bank_transfer',
            ]);

            $response->assertForbidden();
        }

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_completing_order_marks_laptop_sold_and_cancels_other_active_orders(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $completedOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'accepted',
        ]);

        $pendingOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $otherBuyer->id,
            'seller_id' => $seller->id,
            'phone' => '03112223334',
            'address' => 'Other address',
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)->patch(route('orders.complete', $completedOrder));

        $response->assertRedirect();
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'status' => 'sold']);
        $this->assertDatabaseHas('orders', ['id' => $completedOrder->id, 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'cancelled']);
    }

    public function test_order_cannot_be_accepted_or_completed_after_laptop_is_unavailable(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller, ['status' => 'sold']);
        $pendingOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);
        $acceptedOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => User::factory()->create()->id,
            'seller_id' => $seller->id,
            'phone' => '03112223334',
            'address' => 'Another buyer address',
            'payment_method' => 'cod',
            'status' => 'accepted',
        ]);

        $this->actingAs($seller)->patch(route('orders.accept', $pendingOrder))->assertStatus(422);
        $this->actingAs($seller)->patch(route('orders.complete', $acceptedOrder))->assertStatus(422);

        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $acceptedOrder->id, 'status' => 'accepted']);
    }

    public function test_seller_contact_is_hidden_before_order_is_accepted(): void
    {
        $seller = User::factory()->create([
            'phone' => '03000999888',
            'city' => 'Hidden City',
        ]);
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $order = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03112223334',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($buyer)->get(route('laptops.show', $laptop));

        $response->assertOk();
        $response->assertSee('Seller contact will be visible after acceptance.');
        $response->assertDontSee('03000999888');
        $response->assertDontSee('Hidden City');

        $order->status = 'accepted';
        $order->save();

        $response = $this->actingAs($buyer)->get(route('laptops.show', $laptop));
        $response->assertSee('03000999888');
        $response->assertSee('Hidden City');
    }
}
