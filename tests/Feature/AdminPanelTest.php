<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function makeLaptop(User $seller, array $attributes = []): Laptop
    {
        return Laptop::factory()->create(array_merge([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude',
            'processor' => 'Intel i7',
            'ram' => '16 GB',
            'storage' => '512 GB',
            'condition' => 'like_new',
            'price' => 50000,
            'location' => 'Lahore',
            'description' => 'Test',
            'status' => 'available',
            'approval_status' => 'approved',
        ], $attributes));
    }

    public function test_non_admin_gets_403_on_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_renders_inside_the_admin_layout(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Console')
            ->assertSee('Back to Marketplace')
            ->assertSee('Inventory & Orders Overview', false)
            ->assertSee('admin-store-analytics-chart', false)
            ->assertSee('data-inventory-labels', false)
            ->assertSee('data-order-values', false);
    }

    public function test_admin_approves_pending_user(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'user', 'status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.users.approve', $user))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
    }

    public function test_blocking_user_blocks_available_laptops(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $laptop = $this->makeLaptop($user);

        $this->actingAs($admin)
            ->patch(route('admin.users.block', $user), ['blocked_reason' => 'Repeated policy violations'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'blocked']);
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'status' => 'blocked']);
    }

    public function test_admin_cannot_block_an_admin_account(): void
    {
        $admin = $this->makeAdmin();
        $otherAdmin = $this->makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.users.block', $otherAdmin), ['blocked_reason' => 'Test'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id, 'status' => 'active']);
    }

    public function test_laptop_reject_requires_reason(): void
    {
        $admin = $this->makeAdmin();
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller, [
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.laptops.reject', $laptop), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'status' => 'pending']);
    }

    public function test_complaint_can_only_be_actioned_once(): void
    {
        $admin = $this->makeAdmin();
        $complainant = User::factory()->create();
        $against = User::factory()->create();
        $complaint = Complaint::forceCreate([
            'complainant_id' => $complainant->id,
            'against_user_id' => $against->id,
            'subject' => 'Fraud',
            'description' => 'Test complaint',
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.complaints.dismiss', $complaint), ['admin_note' => 'Reviewed'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.complaints.warn', $complaint), ['admin_note' => 'Second action'])
            ->assertStatus(422);

        $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'dismissed']);
    }

    public function test_admin_can_accept_or_reject_pending_orders_and_notify_both_parties(): void
    {
        $admin = $this->makeAdmin();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $acceptedLaptop = $this->makeLaptop($seller, ['model' => 'Acceptable']);
        $rejectedLaptop = $this->makeLaptop($seller, ['model' => 'Rejectable']);
        $acceptedOrder = Order::forceCreate([
            'laptop_id' => $acceptedLaptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);
        $rejectedOrder = Order::forceCreate([
            'laptop_id' => $rejectedLaptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee(route('admin.orders.accept', $acceptedOrder))
            ->assertSee(route('admin.orders.reject', $rejectedOrder));
        $this->actingAs($admin)
            ->get(route('admin.orders.show', $acceptedOrder))
            ->assertOk()
            ->assertSee('Accept order')
            ->assertSee('Reject order');

        $this->actingAs($admin)->patch(route('admin.orders.accept', $acceptedOrder))->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.reject', $rejectedOrder))->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $acceptedOrder->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('orders', ['id' => $rejectedOrder->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $buyer->id,
            'type' => OrderStatusChanged::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $seller->id,
            'type' => OrderStatusChanged::class,
        ]);
        $this->assertSame(
            route('orders.my'),
            $buyer->notifications()->where('type', OrderStatusChanged::class)->firstOrFail()->data['url']
        );
        $this->assertSame(
            route('orders.incoming'),
            $seller->notifications()->where('type', OrderStatusChanged::class)->firstOrFail()->data['url']
        );
    }

    public function test_admin_can_complete_accepted_order_and_cancel_other_open_requests(): void
    {
        $admin = $this->makeAdmin();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);
        $acceptedOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'accepted',
        ]);
        $otherOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $otherBuyer->id,
            'seller_id' => $seller->id,
            'phone' => '03112223334',
            'address' => 'Other buyer address',
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.complete', $acceptedOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'status' => 'sold']);
        $this->assertDatabaseHas('orders', ['id' => $acceptedOrder->id, 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['id' => $otherOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $otherBuyer->id,
            'type' => OrderStatusChanged::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $seller->id,
            'type' => OrderStatusChanged::class,
        ]);
    }

    public function test_admin_can_cancel_open_orders_but_cannot_change_terminal_orders(): void
    {
        $admin = $this->makeAdmin();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);
        $pendingOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);
        $completedOrder = Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.cancel', $pendingOrder))->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.cancel', $completedOrder))->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('orders', ['id' => $completedOrder->id, 'status' => 'completed']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $seller->id,
            'type' => OrderStatusChanged::class,
        ]);
    }

    public function test_sales_report_totals_completed_orders(): void
    {
        $admin = $this->makeAdmin();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        foreach ([30000, 50000] as $price) {
            $laptop = $this->makeLaptop($seller, ['price' => $price]);
            Order::forceCreate([
                'laptop_id' => $laptop->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'phone' => '03001234567',
                'address' => 'Test address',
                'payment_method' => 'cod',
                'status' => 'completed',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('80,000')
            ->assertSee('2');
    }
}
