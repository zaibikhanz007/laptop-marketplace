<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_reject_and_block_laptop_listings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create();
        $pending = Laptop::factory()->pending()->create(['user_id' => $seller->id]);
        $toReject = Laptop::factory()->pending()->create(['user_id' => $seller->id]);
        $toBlock = Laptop::factory()->available()->create(['user_id' => $seller->id]);

        $this->actingAs($admin)->patch(route('admin.laptops.approve', $pending))->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.laptops.reject', $toReject), [
            'rejection_reason' => 'Image quality needs improvement.',
        ])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.laptops.block', $toBlock))->assertRedirect();

        $this->assertDatabaseHas('laptops', ['id' => $pending->id, 'status' => 'available', 'approval_status' => 'approved']);
        $this->assertDatabaseHas('laptops', ['id' => $toReject->id, 'status' => 'rejected', 'approval_status' => 'rejected']);
        $this->assertDatabaseHas('laptops', ['id' => $toBlock->id, 'status' => 'blocked']);
    }

    public function test_user_can_create_complaint_and_admin_can_resolve_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $complainant = User::factory()->create();
        $reported = User::factory()->create();

        $this->actingAs($complainant)->post(route('complaints.store', $reported), [
            'subject' => 'Suspicious listing',
            'description' => 'The seller provided misleading information.',
        ])->assertRedirect(route('complaints.index'));

        $complaint = Complaint::firstOrFail();

        $this->actingAs($admin)->patch(route('admin.complaints.dismiss', $complaint), [
            'admin_note' => 'Reviewed and dismissed.',
        ])->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'dismissed',
            'admin_note' => 'Reviewed and dismissed.',
        ]);
    }

    public function test_admin_block_resolution_blocks_reported_user_and_available_listings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $complainant = User::factory()->create();
        $reported = User::factory()->create();
        $laptop = Laptop::factory()->available()->create(['user_id' => $reported->id]);
        $complaint = Complaint::factory()->create([
            'complainant_id' => $complainant->id,
            'against_user_id' => $reported->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.complaints.block', $complaint), [
            'blocked_reason' => 'Confirmed abuse.',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $reported->id, 'status' => 'blocked']);
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'status' => 'blocked']);
        $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'blocked']);
    }
}
