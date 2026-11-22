<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('services.admin.email');
        $password = config('services.admin.password');

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Set a valid ADMIN_EMAIL before seeding the administrator.');
        }

        if (! is_string($password) || strlen($password) < 16) {
            throw new InvalidArgumentException('Set ADMIN_PASSWORD to a unique password of at least 16 characters before seeding.');
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = env('ADMIN_NAME', 'Administrator');
        $admin->password = Hash::make($password);
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->save();
    }
}
