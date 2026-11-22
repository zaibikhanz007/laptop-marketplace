<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\User;
use App\Notifications\AccountStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_toggle_a_laptop_in_the_wishlist(): void
    {
        $buyer = User::factory()->create();
        $laptop = Laptop::factory()->available()->create();

        $this->actingAs($buyer)->post(route('wishlist.toggle', $laptop))->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $buyer->id, 'laptop_id' => $laptop->id]);

        $this->actingAs($buyer)->post(route('wishlist.toggle', $laptop))->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $buyer->id, 'laptop_id' => $laptop->id]);
    }

    public function test_compare_can_add_remove_and_clear_at_three_laptops(): void
    {
        $seller = User::factory()->create();
        $laptops = Laptop::factory()->available()->count(4)->create(['user_id' => $seller->id]);

        foreach ($laptops->take(3) as $laptop) {
            $this->post(route('compare.add', $laptop))->assertRedirect();
        }

        $this->post(route('compare.add', $laptops[3]))->assertStatus(422);
        $this->assertCount(3, session('compare_laptops'));

        $this->delete(route('compare.remove', $laptops[0]))->assertRedirect();
        $this->assertCount(2, session('compare_laptops'));

        $this->delete(route('compare.clear'))->assertRedirect();
        $this->assertNull(session('compare_laptops'));
    }

    public function test_user_can_mark_a_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new AccountStatusChanged('active'));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
