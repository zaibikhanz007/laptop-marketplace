<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function laptop(User $seller, array $attributes = []): Laptop
    {
        return Laptop::forceCreate(array_merge([
            'user_id' => $seller->id,
            'brand' => 'Dell',
            'model' => 'Latitude',
            'condition' => 'good',
            'price' => 50000,
            'location' => 'Lahore',
            'status' => 'available',
            'approval_status' => 'approved',
        ], $attributes));
    }

    private function order(User $seller, User $buyer, Laptop $laptop): Order
    {
        return Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Private buyer address',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);
    }

    public function test_profile_update_cannot_escalate_role_or_privileged_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'admin',
            'status' => 'active',
            'is_verified' => true,
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('user', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertFalse($user->is_verified);
    }

    public function test_another_user_cannot_edit_a_laptop_or_act_on_an_order(): void
    {
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $otherUser = User::factory()->create();
        $laptop = $this->laptop($owner);
        $order = $this->order($owner, $buyer, $laptop);

        $this->actingAs($otherUser)
            ->get(route('laptops.edit', $laptop))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->patch(route('orders.accept', $order))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('orders.my'))
            ->assertDontSee('Private buyer address');
    }

    public function test_pending_and_blocked_users_cannot_log_in(): void
    {
        foreach (['pending', 'blocked'] as $status) {
            $user = User::factory()->create(['status' => $status]);

            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertSessionHasErrors('email');

            $this->assertGuest();
        }
    }

    public function test_upload_of_non_image_file_is_rejected(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $this->actingAs($seller)->post(route('laptops.store'), [
            'brand' => 'Dell',
            'model' => 'Invalid upload',
            'condition' => 'good',
            'price' => 50000,
            'location' => 'Lahore',
            'images' => [UploadedFile::fake()->create('payload.php', 1, 'application/x-php')],
        ])->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('laptops', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_listing_creation_ignores_user_supplied_moderation_and_feature_flags(): void
    {
        $seller = User::factory()->create();

        $this->actingAs($seller)->post(route('laptops.store'), [
            'brand' => 'Dell',
            'model' => 'Unreviewed listing',
            'condition' => 'good',
            'price' => 50000,
            'location' => 'Lahore',
            'status' => 'available',
            'approval_status' => 'approved',
            'is_featured' => true,
        ])->assertRedirect(route('laptops.mine'));

        $this->assertDatabaseHas('laptops', [
            'user_id' => $seller->id,
            'model' => 'Unreviewed listing',
            'status' => 'pending',
            'approval_status' => 'pending',
            'is_featured' => false,
        ]);
    }

    public function test_users_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Private notification', 'url' => route('home')],
        ]);

        $this->actingAs($otherUser)
            ->get(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_notification_read_redirect_rejects_network_path_urls(): void
    {
        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Test', 'url' => '/\\\\attacker.example'],
        ]);

        $this->actingAs($user)
            ->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_private_local_storage_route_rejects_unsigned_reads(): void
    {
        Storage::disk('local')->put('audit-private.txt', 'private contents');

        $this->get('/storage/audit-private.txt')->assertForbidden();

        Storage::disk('local')->delete('audit-private.txt');
    }

    public function test_seller_and_admin_pages_do_not_expose_unreleased_contacts(): void
    {
        $seller = User::factory()->create([
            'phone' => '03009998877',
            'city' => 'Seller Private City',
            'address' => 'Seller Private Address',
        ]);
        $buyer = User::factory()->create();
        $laptop = $this->laptop($seller, ['location' => 'Listing City']);
        $order = $this->order($seller, $buyer, $laptop);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('sellers.show', $seller))
            ->assertOk()
            ->assertDontSee('03009998877')
            ->assertDontSee('Seller Private Address');

        $this->get(route('laptops.show', $laptop))
            ->assertOk()
            ->assertDontSee('03009998877')
            ->assertDontSee('Seller Private City');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertDontSee('Private buyer address')
            ->assertDontSee('03001234567');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $seller))
            ->assertOk()
            ->assertDontSee('03009998877')
            ->assertDontSee('Seller Private Address');
    }

    public function test_production_error_pages_are_branded_and_do_not_show_exception_details(): void
    {
        config(['app.debug' => false, 'app.env' => 'production']);

        Route::get('/__audit-errors/403', fn () => abort(403));
        Route::get('/__audit-errors/419', fn () => abort(419));
        Route::get('/__audit-errors/500', function () {
            throw new \RuntimeException('Sensitive internal failure detail');
        });

        $this->get('/__audit-errors/403')->assertForbidden()->assertSee('Access denied');
        $this->get('/__audit-errors/419')->assertStatus(419)->assertSee('Page expired');
        $this->get('/__audit-errors/500')
            ->assertServerError()
            ->assertSee('Server error')
            ->assertDontSee('Sensitive internal failure detail');
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found');
    }

    public function test_security_headers_are_added_to_responses(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_default_seeder_creates_only_the_configured_admin_and_demo_seeder_is_repeatable(): void
    {
        config([
            'services.admin.email' => 'configured-admin@example.test',
            'services.admin.password' => 'a-unique-admin-password-123',
            'services.demo_password' => 'a-demo-password-123',
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'configured-admin@example.test',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('laptops', 20);
        $this->assertDatabaseCount('orders', 4);
        $this->assertDatabaseCount('reviews', 2);
        $this->assertDatabaseCount('complaints', 1);
    }
}
