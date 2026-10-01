<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
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
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('klant.home'));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertSame(UserRole::Klant, $user->role);
    }

    public function test_registration_assigns_klant_even_when_a_higher_role_is_submitted(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'klant@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Admin->value,
        ]);

        $user = User::where('email', 'klant@example.com')->firstOrFail();

        $this->assertSame(UserRole::Klant, $user->role);
        $this->get(route('products.index'))->assertForbidden();
    }
}
