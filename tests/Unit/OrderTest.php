<?php

namespace Tests\Unit;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_rules_allow_seller_accept_only_pending_orders(): void
    {
        [$buyer, $seller, $order] = $this->makeOrder('pending');
        $policy = new OrderPolicy;

        $this->assertTrue($policy->accept($seller, $order));
        $this->assertFalse($policy->accept($buyer, $order));
        $this->assertFalse($policy->accept($seller, $this->makeOrder('accepted')[2]));
    }

    public function test_order_status_rules_allow_seller_complete_only_accepted_orders(): void
    {
        [$buyer, $seller, $order] = $this->makeOrder('accepted');
        $policy = new OrderPolicy;

        $this->assertTrue($policy->complete($seller, $order));
        $this->assertFalse($policy->complete($buyer, $order));
        $this->assertFalse($policy->complete($seller, $this->makeOrder('completed')[2]));
    }

    public function test_order_status_rules_allow_buyer_cancel_only_open_orders(): void
    {
        [$buyer, $seller, $pending] = $this->makeOrder('pending');
        $policy = new OrderPolicy;

        $this->assertTrue($policy->cancel($buyer, $pending));
        $this->assertFalse($policy->cancel($seller, $pending));
        $this->assertFalse($policy->cancel($buyer, $this->makeOrder('completed')[2]));
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

        return [$buyer, $seller, $order];
    }
}
