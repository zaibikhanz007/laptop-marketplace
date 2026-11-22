<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $status = 'pending'): Order
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $laptop = Laptop::forceCreate([
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
        ]);

        return Order::forceCreate([
            'laptop_id' => $laptop->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'phone' => '03001234567',
            'address' => 'Buyer address',
            'payment_method' => 'cod',
            'status' => $status,
        ]);
    }

    public function test_buyer_and_seller_can_send_messages_and_receiver_is_notified(): void
    {
        $order = $this->makeOrder();
        $buyer = User::findOrFail($order->buyer_id);
        $seller = User::findOrFail($order->seller_id);

        $this->actingAs($buyer)->post(route('messages.store', $order), [
            'body' => 'Is the laptop still available?',
        ])->assertRedirect(route('messages.index', $order));

        $this->assertDatabaseHas('messages', [
            'order_id' => $order->id,
            'sender_id' => $buyer->id,
            'body' => 'Is the laptop still available?',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $seller->id,
            'type' => NewMessageReceived::class,
        ]);

        $this->actingAs($seller)->post(route('messages.store', $order), [
            'body' => 'Yes, it is available.',
        ])->assertRedirect(route('messages.index', $order));

        $this->assertDatabaseHas('messages', [
            'order_id' => $order->id,
            'sender_id' => $seller->id,
            'body' => 'Yes, it is available.',
        ]);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_stranger_cannot_read_or_send_order_messages(): void
    {
        $order = $this->makeOrder();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get(route('messages.index', $order))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->post(route('messages.store', $order), ['body' => 'Unauthorized message'])
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_cancelled_order_cannot_receive_new_messages(): void
    {
        $this->assertOrderCannotReceiveMessages('cancelled');
    }

    public function test_rejected_order_cannot_receive_new_messages(): void
    {
        $this->assertOrderCannotReceiveMessages('rejected');
    }

    public function test_completed_order_cannot_receive_new_messages(): void
    {
        $this->assertOrderCannotReceiveMessages('completed');
    }

    public function test_opening_conversation_marks_the_other_party_messages_as_read(): void
    {
        $order = $this->makeOrder();
        $buyer = User::findOrFail($order->buyer_id);
        $seller = User::findOrFail($order->seller_id);
        $incoming = Message::forceCreate([
            'order_id' => $order->id,
            'sender_id' => $seller->id,
            'body' => 'Please confirm your address.',
        ]);
        $own = Message::forceCreate([
            'order_id' => $order->id,
            'sender_id' => $buyer->id,
            'body' => 'My address is unchanged.',
        ]);

        $this->actingAs($buyer)->get(route('messages.index', $order))->assertOk();

        $this->assertNotNull($incoming->fresh()->read_at);
        $this->assertNull($own->fresh()->read_at);
    }

    public function test_empty_message_body_is_rejected(): void
    {
        $order = $this->makeOrder();
        $buyer = User::findOrFail($order->buyer_id);

        $this->actingAs($buyer)
            ->from(route('messages.index', $order))
            ->post(route('messages.store', $order), ['body' => ''])
            ->assertSessionHasErrors('body');
    }

    public function test_message_body_longer_than_2000_characters_is_rejected(): void
    {
        $order = $this->makeOrder();
        $buyer = User::findOrFail($order->buyer_id);

        $this->actingAs($buyer)
            ->from(route('messages.index', $order))
            ->post(route('messages.store', $order), ['body' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('body');
    }

    public function test_admin_can_read_but_cannot_send_order_messages(): void
    {
        $order = $this->makeOrder();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('messages.index', $order))->assertOk();
        $this->actingAs($admin)
            ->post(route('messages.store', $order), ['body' => 'Admin message'])
            ->assertForbidden();
    }

    private function assertOrderCannotReceiveMessages(string $status): void
    {
        $order = $this->makeOrder($status);
        $buyer = User::findOrFail($order->buyer_id);

        $this->actingAs($buyer)
            ->get(route('messages.index', $order))
            ->assertForbidden();
        $this->actingAs($buyer)
            ->post(route('messages.store', $order), ['body' => 'Can we continue this conversation?'])
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }
}
