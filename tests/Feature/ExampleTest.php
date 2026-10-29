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
        $response->assertDontSee('Overzicht Leveranciers');
        $response->assertDontSee('Overzicht producten Jamin');
    }

    public function test_medewerker_sees_overview_links_on_the_homepage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertSee('Overzicht Leveranciers');
        $response->assertSee(route('leveranciers.index'), false);
        $response->assertSee('Overzicht producten Jamin');
        $response->assertSee(route('products.index'), false);
        $response->assertDontSee('Kies of je wilt inloggen of een account wilt aanmaken.');
    }

    public function test_klant_is_redirected_to_the_customer_home(): void
    {
        $klant = User::factory()->klant()->create();

        $response = $this->actingAs($klant)->get('/');

        $response->assertRedirect(route('klant.home'));
    }
}
