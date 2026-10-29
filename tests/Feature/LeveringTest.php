<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeveringTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_delivery_form_to_login(): void
    {
        $response = $this->get(route('leveranciers.levering.create', [1, 1]));

        $response->assertRedirect(route('login'));
    }

    public function test_klant_cannot_store_a_delivery(): void
    {
        $klant = User::factory()->klant()->create();

        $response = $this->actingAs($klant)->post(route('leveranciers.levering.store', [1, 1]), [
            'aantal' => 12,
            'datum_eerstvolgende_levering' => '2026-10-20',
        ]);

        $response->assertForbidden();
    }

    public function test_product_row_links_to_the_delivery_form(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.producten', 1));

        $response->assertSee(route('leveranciers.levering.create', [1, 1]), false);
    }

    public function test_medewerker_sees_the_delivery_form_for_a_supplied_product(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.levering.create', [1, 1]));

        $response->assertSeeInOrder([
            'Levering product',
            'Mintnopjes',
            'Venco',
            'Bert van Linge',
            '06-28493827',
            'Aantal producteenheden',
            'Datum eerstvolgende levering',
            'Sla op',
            'Terug',
            'Home',
        ]);
    }

    public function test_unknown_supplier_product_combination_is_not_found(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('leveranciers.levering.create', [6, 1]));

        $response->assertNotFound();
    }

    public function test_storing_an_unknown_supplier_product_combination_is_not_found(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->post(route('leveranciers.levering.store', [6, 1]), [
            'aantal' => 12,
            'datum_eerstvolgende_levering' => '2026-10-20',
        ]);

        $response->assertNotFound();
    }

    public function test_medewerker_can_register_a_delivery_and_increase_stock(): void
    {
        $this->travelTo('2026-10-08');
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->post(route('leveranciers.levering.store', [1, 1]), [
            'aantal' => 12,
            'datum_eerstvolgende_levering' => '2026-10-08',
        ]);

        $response->assertRedirect(route('leveranciers.producten', 1));
        $this->assertDatabaseHas('ProductPerLeverancier', [
            'LeverancierId' => 1,
            'ProductId' => 1,
            'DatumLevering' => '2026-10-08',
            'Aantal' => 12,
            'DatumEerstVolgendeLevering' => '2026-10-08',
        ]);
        $this->assertDatabaseHas('Magazijn', [
            'ProductId' => 1,
            'AantalAanwezig' => 465,
        ]);
    }

    public function test_delivery_for_an_active_product_without_stock_sets_the_delivered_amount(): void
    {
        $this->travelTo('2026-10-08');
        $medewerker = User::factory()->magazijnMedewerker()->create();
        DB::table('Magazijn')->where('ProductId', 1)->update(['AantalAanwezig' => null]);

        $response = $this->actingAs($medewerker)->post(route('leveranciers.levering.store', [1, 1]), [
            'aantal' => 5,
            'datum_eerstvolgende_levering' => '2026-11-01',
        ]);

        $response->assertRedirect(route('leveranciers.producten', 1));
        $this->assertDatabaseHas('Magazijn', [
            'ProductId' => 1,
            'AantalAanwezig' => 5,
        ]);
    }

    public function test_inactive_product_delivery_shows_message_and_does_not_change_stock(): void
    {
        $this->travelTo('2026-10-15');
        $medewerker = User::factory()->magazijnMedewerker()->create();
        $before = DB::table('ProductPerLeverancier')->count();

        $response = $this->actingAs($medewerker)->post(route('leveranciers.levering.store', [4, 10]), [
            'aantal' => 30,
            'datum_eerstvolgende_levering' => '2026-10-15',
        ]);

        $response->assertSee('Levering product');
        $response->assertSee('Het product Winegums van de leverancier Basset wordt niet meer geproduceerd');
        $response->assertSee('4000', false);
        $response->assertSee(route('leveranciers.producten', 4), false);
        $this->assertSame($before, DB::table('ProductPerLeverancier')->count());
        $this->assertDatabaseHas('Magazijn', [
            'ProductId' => 10,
            'AantalAanwezig' => null,
        ]);
    }

    public function test_delivery_requires_an_amount_and_a_next_delivery_date(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();
        $before = DB::table('ProductPerLeverancier')->count();

        $response = $this->actingAs($medewerker)
            ->from(route('leveranciers.levering.create', [1, 1]))
            ->post(route('leveranciers.levering.store', [1, 1]), []);

        $response->assertRedirect(route('leveranciers.levering.create', [1, 1]));
        $response->assertSessionHasErrors([
            'aantal' => 'Vul het aantal producteenheden in.',
            'datum_eerstvolgende_levering' => 'Vul de datum van de eerstvolgende levering in.',
        ]);
        $this->assertSame($before, DB::table('ProductPerLeverancier')->count());
    }

    public function test_delivery_rejects_an_amount_below_one(): void
    {
        $this->travelTo('2026-10-08');
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)
            ->from(route('leveranciers.levering.create', [1, 1]))
            ->post(route('leveranciers.levering.store', [1, 1]), [
                'aantal' => 0,
                'datum_eerstvolgende_levering' => '2026-10-20',
            ]);

        $response->assertSessionHasErrors([
            'aantal' => 'Het aantal producteenheden moet minstens 1 zijn.',
        ]);
    }

    public function test_next_delivery_date_must_not_be_in_the_past(): void
    {
        $this->travelTo('2026-10-08');
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)
            ->from(route('leveranciers.levering.create', [1, 1]))
            ->post(route('leveranciers.levering.store', [1, 1]), [
                'aantal' => 4,
                'datum_eerstvolgende_levering' => '2026-10-07',
            ]);

        $response->assertSessionHasErrors([
            'datum_eerstvolgende_levering' => 'De datum van de eerstvolgende levering mag niet in het verleden liggen.',
        ]);
    }

    public function test_delivery_form_escapes_rejected_input(): void
    {
        $this->travelTo('2026-10-08');
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)
            ->from(route('leveranciers.levering.create', [1, 1]))
            ->post(route('leveranciers.levering.store', [1, 1]), [
                'aantal' => '"><script>alert(1)</script>',
                'datum_eerstvolgende_levering' => '2026-10-20',
            ]);

        $response->assertRedirect(route('leveranciers.levering.create', [1, 1]));

        $form = $this->actingAs($medewerker)->get(route('leveranciers.levering.create', [1, 1]));

        $form->assertDontSee('<script>alert(1)</script>', false);
        $form->assertSee('"><script>alert(1)</script>');
    }
}
