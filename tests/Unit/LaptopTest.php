<?php

namespace Tests\Unit;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaptopTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_scope_returns_only_approved_available_listings(): void
    {
        $seller = User::factory()->create();
        $available = Laptop::factory()->available()->create(['user_id' => $seller->id]);
        Laptop::factory()->pending()->create(['user_id' => $seller->id]);
        Laptop::factory()->sold()->create(['user_id' => $seller->id]);

        $this->assertSame([$available->id], Laptop::available()->pluck('id')->all());
    }

    public function test_featured_scope_excludes_expired_and_unfeatured_listings(): void
    {
        $seller = User::factory()->create();
        $featured = Laptop::factory()->featured()->create(['user_id' => $seller->id]);
        Laptop::factory()->featured()->create([
            'user_id' => $seller->id,
            'featured_until' => today()->subDay(),
        ]);
        Laptop::factory()->available()->create(['user_id' => $seller->id]);

        $this->assertSame([$featured->id], Laptop::featured()->pluck('id')->all());
    }

    public function test_filter_scope_applies_search_brand_city_condition_and_price_bounds(): void
    {
        $seller = User::factory()->create();
        $match = Laptop::factory()->available()->create([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude 7420',
            'condition' => 'like_new',
            'location' => 'Lahore',
            'price' => 100000,
        ]);
        Laptop::factory()->available()->create([
            'user_id' => $seller->id,
            'brand' => 'HP',
            'condition' => 'like_new',
            'location' => 'Lahore',
            'price' => 100000,
        ]);
        Laptop::factory()->available()->create([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'condition' => 'like_new',
            'location' => 'Karachi',
            'price' => 100000,
        ]);

        $result = Laptop::filter([
            'q' => '7420',
            'brand' => 'Dell',
            'condition' => 'like_new',
            'city' => 'Lahore',
            'min_price' => 90000,
            'max_price' => 110000,
        ])->pluck('id')->all();

        $this->assertSame([$match->id], $result);
    }

    public function test_sort_by_orders_prices_newest_and_featured_first(): void
    {
        $seller = User::factory()->create();
        $cheap = Laptop::factory()->available()->create(['user_id' => $seller->id, 'price' => 20000]);
        $expensive = Laptop::factory()->available()->create(['user_id' => $seller->id, 'price' => 300000]);

        $this->assertSame(
            [$cheap->id, $expensive->id],
            Laptop::sortBy('price_low')->pluck('id')->all()
        );
        $this->assertSame(
            [$expensive->id, $cheap->id],
            Laptop::sortBy('price_high')->pluck('id')->all()
        );

        $featured = Laptop::factory()->featured()->create(['user_id' => $seller->id]);

        $this->assertSame($featured->id, Laptop::sortBy(null)->first()->id);
        $this->assertSame($expensive->id, Laptop::sortBy('price_high')->first()->id);
    }
}
