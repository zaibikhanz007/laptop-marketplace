<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewPurchaseRequest;
use App\Notifications\OrderStatusChanged;
use Database\Seeders\MarketplaceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceToolsTest extends TestCase
{
    use RefreshDatabase;

    private function makeLaptop(User $seller, array $attributes = []): Laptop
    {
        return Laptop::factory()->create(array_merge([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude',
            'processor' => 'Intel Core i7',
            'ram' => '16 GB',
            'storage' => '512 GB SSD',
            'condition' => 'like_new',
            'price' => 100000,
            'location' => 'Karachi',
            'description' => 'Test listing',
            'status' => 'available',
            'approval_status' => 'approved',
        ], $attributes));
    }

    public function test_wishlist_toggle_adds_and_removes_available_laptop(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $this->actingAs($user)->post(route('wishlist.toggle', $laptop))->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'laptop_id' => $laptop->id]);

        $this->actingAs($user)->post(route('wishlist.toggle', $laptop))->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'laptop_id' => $laptop->id]);
    }

    public function test_wishlist_rejects_unavailable_laptop(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller, ['status' => 'sold']);

        $this->actingAs($user)->post(route('wishlist.toggle', $laptop))->assertStatus(422);
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_purchase_request_and_acceptance_create_notifications(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $this->actingAs($buyer)->post(route('orders.store', $laptop), [
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
        ])->assertRedirect(route('orders.my'));

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $seller->id,
            'type' => NewPurchaseRequest::class,
        ]);

        $this->actingAs($seller)->patch(route('orders.accept', $order))->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $buyer->id,
            'type' => OrderStatusChanged::class,
        ]);
    }

    public function test_featured_scope_excludes_expired_featured_listings(): void
    {
        $seller = User::factory()->create();
        $active = $this->makeLaptop($seller, ['is_featured' => true, 'featured_until' => today()->addDay()]);
        $expired = $this->makeLaptop($seller, ['model' => 'Expired', 'is_featured' => true, 'featured_until' => today()->subDay()]);
        $noExpiry = $this->makeLaptop($seller, ['model' => 'No expiry', 'is_featured' => true, 'featured_until' => null]);

        $featuredIds = Laptop::featured()->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$active->id, $noExpiry->id], $featuredIds);
        $this->assertNotContains($expired->id, $featuredIds);
    }

    public function test_compare_is_limited_to_three_available_laptops(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $laptops = collect(range(1, 4))->map(fn ($number) => $this->makeLaptop($seller, ['model' => "Model {$number}"]));

        foreach ($laptops->take(3) as $laptop) {
            $this->post(route('compare.add', $laptop))->assertRedirect();
        }

        $this->post(route('compare.add', $laptops[3]))->assertStatus(422);
        $this->assertCount(3, session('compare_laptops'));
    }

    public function test_city_filter_and_explicit_price_sort_return_expected_laptops(): void
    {
        $seller = User::factory()->create();
        $cheap = $this->makeLaptop($seller, ['brand' => 'Alpha', 'model' => 'Cheap', 'price' => 40000, 'location' => 'Lahore']);
        $expensive = $this->makeLaptop($seller, ['brand' => 'Beta', 'model' => 'Expensive', 'price' => 90000, 'location' => 'Lahore']);
        $otherCity = $this->makeLaptop($seller, ['brand' => 'Gamma', 'model' => 'Other', 'price' => 10000, 'location' => 'Karachi']);

        $response = $this->actingAs($seller)->get(route('laptops.index', ['city' => 'Lahore', 'sort' => 'price_low']));

        $response->assertOk()->assertSeeInOrder(['Alpha Cheap', 'Beta Expensive'])->assertDontSee('Gamma Other');
        $this->assertNotSame($cheap->id, $expensive->id);
        $this->assertNotSame($otherCity->id, $cheap->id);
    }

    public function test_laptop_card_shows_local_image_when_listing_has_no_uploaded_photos(): void
    {
        $seller = User::factory()->create();
        $laptop = $this->makeLaptop($seller);

        $this->actingAs($seller)->get(route('laptops.index'))
            ->assertOk()
            ->assertSee('images/laptop-placeholder.svg')
            ->assertSee($laptop->brand.' '.$laptop->model);
    }

    public function test_marketplace_accepts_empty_optional_filter_values(): void
    {
        $this->actingAs(User::factory()->create())->get(route('laptops.index', [
            'q' => '',
            'brand' => '',
            'condition' => '',
            'city' => '',
            'min_price' => '',
            'max_price' => '',
            'sort' => '',
        ]))->assertOk();
    }

    public function test_marketplace_catalog_seeder_adds_fifteen_available_laptops_idempotently(): void
    {
        User::factory()->create();

        $this->seed(MarketplaceCatalogSeeder::class);
        $this->assertSame(15, Laptop::available()->count());
        $this->assertGreaterThanOrEqual(10, Laptop::available()->distinct()->count('brand'));

        $this->seed(MarketplaceCatalogSeeder::class);
        $this->assertSame(15, Laptop::available()->count());
    }
}
