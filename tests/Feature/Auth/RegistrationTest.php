<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '03001234567',
            'city' => 'Lahore',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '03001234567',
            'city' => 'Lahore',
            'status' => 'pending',
        ]);
        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }
}
