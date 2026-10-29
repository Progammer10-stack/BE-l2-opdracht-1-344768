<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeverancierOverzichtTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_supplier_overview_to_login(): void
    {
        $response = $this->get(route('leveranciers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_klant_cannot_view_supplier_overview(): void
    {
        $klant = User::factory()->klant()->create();

        $response = $this->actingAs($klant)->get(route('leveranciers.index'));

        $response->assertForbidden();
    }

    public function test_medewerker_sees_suppliers_sorted_by_distinct_product_count_descending(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Astra Sweets',
            'De Bron',
            'Haribo',
            'Venco',
            'Basset',
            'Quality Street',
        ]);
        $response->assertSee('L1029384719');
        $response->assertSee('06-28493827');
        $response->assertSee('Johan Nooij');
    }

    public function test_medewerker_sees_venco_products_sorted_by_stock_descending(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.producten', 1));

        $response->assertOk();
        $response->assertSee('Bert van Linge');
        $response->assertSee('L1029384719');
        $response->assertSeeInOrder([
            'Mintnopjes',
            '453',
            '5 kg',
            '18-11-2024',
            'Schoolkrijt',
            '400',
            'Honingdrop',
        ]);
    }

    public function test_supplier_without_deliveries_shows_the_empty_message(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.producten', 6));

        $response->assertOk();
        $response->assertSee('Quality Street');
        $response->assertSeeInOrder([
            'Naam product',
            'Aantal in Magazijn',
            'Verpakkingseenheid',
            'Laatste levering',
            'Nieuwe levering',
            'Dit bedrijf heeft tot nu toe geen producten geleverd aan Jamin',
        ]);
        $response->assertSee('3000', false);
        $response->assertDontSee('Mintnopjes');
    }
}
