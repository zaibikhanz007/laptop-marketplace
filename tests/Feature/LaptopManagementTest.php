<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaptopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_cannot_edit_another_sellers_laptop(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $laptop = Laptop::factory()->available()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger)->get(route('laptops.edit', $laptop))->assertForbidden();
        $this->actingAs($stranger)->put(route('laptops.update', $laptop), [
            'brand' => 'Changed',
            'model' => 'Changed',
            'condition' => 'new',
            'price' => 50000,
            'location' => 'Lahore',
        ])->assertForbidden();

        $this->assertDatabaseHas('laptops', ['id' => $laptop->id, 'brand' => $laptop->brand]);
    }

    public function test_laptop_upload_rejects_more_than_six_images(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)->post(route('laptops.store'), $this->listingData([
            'images' => collect(range(1, 7))->map(fn ($i) => UploadedFile::fake()->create("laptop-{$i}.jpg", 20, 'image/jpeg'))->all(),
        ]));

        $response->assertSessionHasErrors('images');
        $this->assertDatabaseCount('laptops', 0);
    }

    public function test_laptop_upload_rejects_images_larger_than_two_megabytes(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)->post(route('laptops.store'), $this->listingData([
            'images' => [UploadedFile::fake()->create('large.jpg', 2049, 'image/jpeg')],
        ]));

        $response->assertSessionHasErrors('images.0');
        $this->assertDatabaseCount('laptops', 0);
    }

    public function test_laptop_upload_rejects_non_image_mime_types(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)->post(route('laptops.store'), $this->listingData([
            'images' => [UploadedFile::fake()->create('document.pdf', 20, 'application/pdf')],
        ]));

        $response->assertSessionHasErrors('images.0');
        $this->assertDatabaseCount('laptops', 0);
    }

    private function listingData(array $overrides = []): array
    {
        return array_merge([
            'brand' => 'Dell',
            'model' => 'Latitude',
            'processor' => 'Intel i7',
            'ram' => '16 GB',
            'storage' => '512 GB SSD',
            'condition' => 'like_new',
            'price' => 100000,
            'location' => 'Lahore',
            'description' => 'A test laptop listing.',
        ], $overrides);
    }
}
