<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_home_with_login_and_registration(): void
    {
        $response = $this->get('/');

        $response->assertSee('Inloggen');
        $response->assertSee('Registreren');
        $response->assertSee(route('login'), false);
        $response->assertSee(route('register'), false);
    }

    public function test_authenticated_user_is_redirected_to_the_product_overview(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('products.index'));
    }

    public function test_klant_is_redirected_to_the_customer_home(): void
    {
        $klant = User::factory()->klant()->create();

        $response = $this->actingAs($klant)->get('/');

        $response->assertRedirect(route('klant.home'));
    }
}
