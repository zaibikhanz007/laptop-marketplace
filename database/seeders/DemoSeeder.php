<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('services.demo_password');
        if (! is_string($password) || strlen($password) < 12) {
            throw new InvalidArgumentException('Set DEMO_PASSWORD to a unique password of at least 12 characters before seeding demo data.');
        }

        $this->call(DatabaseSeeder::class);
        $hash = Hash::make($password);
        $users = collect();

        foreach (range(1, 5) as $number) {
            $users->push($this->user(
                "demo.active{$number}@example.test",
                "Demo Active {$number}",
                'active',
                $hash,
                $number
            ));
        }

        foreach (range(1, 3) as $number) {
            $users->push($this->user(
                "demo.pending{$number}@example.test",
                "Demo Pending {$number}",
                'pending',
                $hash,
                $number + 5
            ));
        }

        $brands = ['Dell', 'HP', 'Lenovo', 'Apple', 'Acer'];
        $cities = ['Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Peshawar'];
        $statuses = [
            'available', 'available', 'available', 'available', 'available',
            'available', 'available', 'available', 'available', 'available',
            'pending', 'pending', 'pending', 'pending',
            'sold', 'sold',
            'rejected', 'rejected',
            'blocked', 'blocked',
        ];
        $laptops = collect();

        foreach (range(1, 20) as $number) {
            $status = $statuses[$number - 1];
            $laptop = Laptop::firstOrNew([
                'user_id' => $users[($number - 1) % 5]->id,
                'brand' => $brands[($number - 1) % count($brands)],
                'model' => "Demo Model {$number}",
            ]);
            $laptop->forceFill(Laptop::factory()->state([
                'user_id' => $users[($number - 1) % 5]->id,
                'brand' => $brands[($number - 1) % count($brands)],
                'model' => "Demo Model {$number}",
                'processor' => 'Intel Core i5',
                'ram' => $number % 2 ? '8 GB' : '16 GB',
                'storage' => $number % 2 ? '256 GB SSD' : '512 GB SSD',
                'condition' => ['like_new', 'good', 'fair'][$number % 3],
                'price' => 45000 + ($number * 7500),
                'location' => $cities[($number - 1) % count($cities)],
                'description' => 'Sample listing for local demonstrations.',
                'status' => $status,
                'approval_status' => match ($status) {
                    'pending' => 'pending',
                    'rejected' => 'rejected',
                    default => 'approved',
                },
                'is_featured' => $status === 'available' && $number <= 2,
                'featured_until' => $status === 'available' && $number === 2 ? today()->addDays(30) : null,
            ])->make()->getAttributes())->save();
            $laptops->push($laptop);
        }

        $activeUsers = $users->take(5)->values();
        $orderDefinitions = [
            [14, 0, 'completed'],
            [15, 1, 'completed'],
            [0, 2, 'pending'],
            [1, 3, 'accepted'],
        ];
        $orders = collect();

        foreach ($orderDefinitions as [$laptopIndex, $buyerIndex, $status]) {
            $laptop = $laptops[$laptopIndex];
            $order = Order::firstOrNew([
                'laptop_id' => $laptop->id,
                'buyer_id' => $activeUsers[$buyerIndex]->id,
            ]);
            $order->forceFill(Order::factory()->state([
                'seller_id' => $laptop->user_id,
                'laptop_id' => $laptop->id,
                'buyer_id' => $activeUsers[$buyerIndex]->id,
                'phone' => '03000000000',
                'address' => 'Demo address, ' . $laptop->location,
                'payment_method' => 'cod',
                'status' => $status,
            ])->make()->getAttributes())->save();
            $orders->push($order);
        }

        foreach ($orders->where('status', 'completed')->values() as $index => $order) {
            $review = Review::firstOrNew(['order_id' => $order->id]);
            $review->forceFill(Review::factory()->state([
                'order_id' => $order->id,
                'reviewer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'rating' => $index === 0 ? 5 : 4,
                'comment' => 'Sample verified-purchase review.',
            ])->make()->getAttributes())->save();
        }

        $complaint = Complaint::firstOrNew([
            'complainant_id' => $activeUsers[0]->id,
            'against_user_id' => $activeUsers[1]->id,
            'subject' => 'Demo complaint',
        ]);
        $complaint->forceFill(Complaint::factory()->state([
            'complainant_id' => $activeUsers[0]->id,
            'against_user_id' => $activeUsers[1]->id,
            'subject' => 'Demo complaint',
            'description' => 'Sample complaint for moderation demonstrations.',
            'status' => 'open',
        ])->make()->getAttributes())->save();
    }

    private function user(string $email, string $name, string $status, string $passwordHash, int $number): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill(User::factory()->state([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'role' => 'user',
            'status' => $status,
            'phone' => '0300000000' . $number,
            'city' => ['Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Peshawar'][$number % 5],
        ])->make()->getAttributes())->save();

        return $user;
    }
}
