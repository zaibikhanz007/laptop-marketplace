<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAccountTest extends TestCase
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
            'payment_account_title' => 'Seller Account Title',
            'payment_account_number' => 'PK00TEST123456789',
            'payment_bank_name' => 'Test Bank',
        ], $attributes));
    }

    private function makeOrder(Laptop $laptop, User $buyer, string $status): Order
    {
        return Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $laptop->user_id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'bank_transfer',
            'status' => $status,
        ]);
    }

    public function test_payment_account_details_are_hidden_on_the_public_laptop_page(): void
    {
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $response = $this->get(route('laptops.show', $laptop));

        $response->assertOk();
        $response->assertDontSee('Seller Account Title');
        $response->assertDontSee('PK00TEST123456789');
        $response->assertDontSee('Test Bank');
    }

    public function test_payment_account_details_are_hidden_from_the_buyer_when_order_is_pending(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);
        $this->makeOrder($laptop, $buyer, 'pending');

        $response = $this->actingAs($buyer)->get(route('orders.my'));

        $response->assertOk();
        $response->assertDontSee('Seller Account Title');
        $response->assertDontSee('PK00TEST123456789');
        $response->assertDontSee('Test Bank');
    }

    public function test_payment_account_details_are_visible_to_the_buyer_when_order_is_accepted(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);
        $this->makeOrder($laptop, $buyer, 'accepted');

        $response = $this->actingAs($buyer)->get(route('orders.my'));

        $response->assertOk();
        $response->assertSee('Seller Account Title');
        $response->assertSee('PK00TEST123456789');
        $response->assertSee('Test Bank');
    }

    public function test_payment_account_details_are_hidden_from_an_unrelated_user(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $unrelatedUser = User::factory()->create();
        $laptop = $this->makeLaptop($seller);
        $this->makeOrder($laptop, $buyer, 'accepted');

        $response = $this->actingAs($unrelatedUser)->get(route('orders.my'));

        $response->assertOk();
        $response->assertDontSee('Seller Account Title');
        $response->assertDontSee('PK00TEST123456789');
        $response->assertDontSee('Test Bank');
    }

    public function test_buyer_can_choose_bank_transfer_without_seller_account_details(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller, [
            'payment_account_title' => null,
            'payment_account_number' => null,
            'payment_bank_name' => null,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.store', $laptop), [
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('orders.my'));
        $this->assertDatabaseHas('orders', [
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);
    }

    public function test_accepted_bank_transfer_order_shows_fallback_when_seller_has_no_account_details(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller, [
            'payment_account_title' => null,
            'payment_account_number' => null,
            'payment_bank_name' => null,
        ]);
        $this->makeOrder($laptop, $buyer, 'accepted');

        $response = $this->actingAs($buyer)->get(route('orders.my'));

        $response->assertOk();
        $response->assertSee('Seller will share account details');
    }
}
