<?php

namespace Database\Seeders;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LaptopDemoSeeder extends Seeder
{
    public function run(): void
    {
        $json = database_path('seeders/laptops.json');
        $imgDir = database_path('seeders/laptop_images');

        if (! File::exists($json)) {
            $this->command->error('database/seeders/laptops.json nahi mila.');
            return;
        }

        // 5 demo sellers (active)
        $cities = ['Lahore', 'Karachi', 'Islamabad', 'Faisalabad', 'Rawalpindi'];
        $sellers = collect(range(1, 5))->map(fn ($i) => User::firstOrCreate(
            ['email' => "seller{$i}@laptopstore.test"],
            [
                'name'     => "Demo Seller {$i}",
                'password' => Hash::make('password'),
                'role'     => 'user',
                'status'   => 'active',
                'phone'    => '0300000000' . $i,
                'city'     => $cities[$i - 1],
            ]
        ));

        $hasCover = Schema::hasColumn('laptop_images', 'is_cover');

        foreach (json_decode(File::get($json), true) as $i => $row) {
            $laptop = Laptop::firstOrNew([
                'brand' => $row['brand'],
                'model' => $row['model'],
            ]);
            if (! $laptop->exists) {
                $laptop->user_id = $sellers[$i % $sellers->count()]->id;
            }
            $laptop->forceFill([
                'brand'       => $row['brand'],
                'model'       => $row['model'],
                'processor'   => $row['processor'],
                'ram'         => $row['ram'],
                'storage'     => $row['storage'],
                'condition'   => $row['condition'],
                'price'       => $row['price'],
                'location'    => $row['location'],
                'description' => $row['description'],
                'status'      => $row['status'],
                'approval_status' => 'approved',
                'is_featured' => false,
                'featured_until' => null,
            ])->save();

            foreach ($laptop->images()->get() as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            foreach (array_slice($row['images'], 0, 1) as $k => $rel) {
                $src = $imgDir . '/' . $rel;
                if (! File::exists($src)) {
                    continue;
                }
                $name = 'laptops/' . uniqid('', true) . '.jpg';
                Storage::disk('public')->put($name, File::get($src));

                $data = ['path' => $name];
                if ($hasCover) {
                    $data['is_cover'] = $k === 0;
                }
                $laptop->images()->create($data);
            }
        }

        $this->command->info('40 demo laptops + images seeded.');
    }
}
