<?php

namespace Database\Seeders;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class MarketplaceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()
            ->where('role', 'user')
            ->where('status', 'active')
            ->oldest('id')
            ->first();

        if (! $seller) {
            throw new RuntimeException('Create an active user account before adding the sample laptop catalog.');
        }

        $catalog = [
            ['Dell', 'Latitude 7440', 'Intel Core i7-1365U', '16 GB', '512 GB SSD', 'like_new', 238000, 'Karachi'],
            ['HP', 'EliteBook 840 G8', 'Intel Core i7-1165G7', '16 GB', '512 GB SSD', 'good', 168000, 'Lahore'],
            ['Lenovo', 'ThinkPad X1 Carbon Gen 9', 'Intel Core i7-1165G7', '16 GB', '1 TB SSD', 'like_new', 245000, 'Islamabad'],
            ['Apple', 'MacBook Air M1', 'Apple M1', '8 GB', '256 GB SSD', 'good', 205000, 'Rawalpindi'],
            ['Acer', 'Swift 3', 'AMD Ryzen 7 5700U', '16 GB', '512 GB SSD', 'good', 142000, 'Peshawar'],
            ['Asus', 'ZenBook 14', 'Intel Core i5-1240P', '16 GB', '512 GB SSD', 'like_new', 198000, 'Karachi'],
            ['MSI', 'Modern 15', 'Intel Core i7-1255U', '16 GB', '512 GB SSD', 'good', 185000, 'Lahore'],
            ['Microsoft', 'Surface Laptop 4', 'Intel Core i5-1135G7', '8 GB', '256 GB SSD', 'fair', 158000, 'Islamabad'],
            ['Samsung', 'Galaxy Book Pro', 'Intel Core i7-1165G7', '16 GB', '512 GB SSD', 'like_new', 212000, 'Rawalpindi'],
            ['Huawei', 'MateBook D15', 'Intel Core i5-1135G7', '8 GB', '512 GB SSD', 'good', 132000, 'Peshawar'],
            ['Razer', 'Blade Stealth 13', 'Intel Core i7-1165G7', '16 GB', '512 GB SSD', 'fair', 225000, 'Karachi'],
            ['Gigabyte', 'Aero 14', 'Intel Core i7-12700H', '16 GB', '1 TB SSD', 'good', 275000, 'Lahore'],
            ['Dell', 'Precision 5560', 'Intel Core i7-11800H', '32 GB', '1 TB SSD', 'good', 265000, 'Islamabad'],
            ['HP', 'Envy x360 13', 'AMD Ryzen 5 5600U', '16 GB', '512 GB SSD', 'like_new', 178000, 'Rawalpindi'],
            ['Lenovo', 'Legion 5', 'AMD Ryzen 7 5800H', '16 GB', '1 TB SSD', 'fair', 248000, 'Peshawar'],
        ];

        foreach ($catalog as [$brand, $model, $processor, $ram, $storage, $condition, $price, $city]) {
            $laptop = Laptop::firstOrNew([
                'user_id' => $seller->id,
                'brand' => $brand,
                'model' => $model,
            ]);
            $laptop->forceFill([
                'processor' => $processor,
                'ram' => $ram,
                'storage' => $storage,
                'condition' => $condition,
                'price' => $price,
                'location' => $city,
                'description' => 'Sample marketplace listing. Contact the seller to learn more.',
                'status' => 'available',
                'approval_status' => 'approved',
                'is_featured' => false,
                'featured_until' => null,
            ])->save();
        }
    }
}
