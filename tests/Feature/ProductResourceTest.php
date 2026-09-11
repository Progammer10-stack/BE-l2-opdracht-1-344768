<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_products_index_to_login(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_medewerker_can_view_products_index(): void
    {
        $this->seed();
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Overzicht Magazijn Jamin');
        $response->assertSeeInOrder([
            '8719587231278',
            '8719587321237',
            '8719587321441',
        ]);
        $response->assertSee('Mintnopjes');
        $response->assertSee('Winegums');
        $response->assertSee('Zoute Ruitjes');
        $response->assertSee('NULL');
    }

    public function test_mintnopjes_delivery_information_is_shown(): void
    {
        $this->seed();
        $medewerker = User::where('email', 'magazijn@example.com')->firstOrFail();
        $mintnopjes = Product::where('Naam', 'Mintnopjes')->firstOrFail();

        $response = $this->actingAs($medewerker)
            ->get(route('products.show', $mintnopjes));

        $response->assertOk();
        $response->assertSee('Venco');
        $response->assertSee('Bert van Linge');
        $response->assertSee('18-10-2024');
        $response->assertSee('21');
        $response->assertSee('25-10-2024');
    }

    public function test_winegums_shows_no_stock_message(): void
    {
        $this->seed();
        $medewerker = User::where('email', 'magazijn@example.com')->firstOrFail();
        $winegums = Product::where('Naam', 'Winegums')->firstOrFail();

        $response = $this->actingAs($medewerker)
            ->get(route('products.show', $winegums));

        $response->assertOk();
        $response->assertSeeText('Er is van dit product op dit moment geen voorraad aanwezig');
        $response->assertSee('30-10-2024');
        $response->assertSee('4000');
    }

    public function test_zoute_ruitjes_allergens_are_shown_sorted_by_name(): void
    {
        $this->seed();
        $medewerker = User::where('email', 'magazijn@example.com')->firstOrFail();
        $zouteRuitjes = Product::where('Naam', 'Zoute Ruitjes')->firstOrFail();

        $response = $this->actingAs($medewerker)
            ->get(route('products.allergenen', $zouteRuitjes));

        $response->assertOk();
        $response->assertSee('Overzicht Allergenen');
        $response->assertSee('Naam Product');
        $response->assertSee('Zoute Ruitjes');
        $response->assertSee('8719587323256');
        $response->assertSeeInOrder([
            'Gluten',
            'Lactose',
            'Soja',
        ]);
    }

    public function test_cola_flesjes_shows_no_allergens_message_and_redirects(): void
    {
        $this->seed();
        $medewerker = User::where('email', 'magazijn@example.com')->firstOrFail();
        $colaFlesjes = Product::where('Naam', 'Cola Flesjes')->firstOrFail();

        $response = $this->actingAs($medewerker)
            ->get(route('products.allergenen', $colaFlesjes));

        $response->assertOk();
        $response->assertSee('In dit product zitten geen stoffen die een allergische reactie kunnen veroorzaken');
        $response->assertSee('4000');
    }
}
