<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpecificationTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_specification_tables_contain_the_documented_row_counts(): void
    {
        $this->assertDatabaseCount('Product', 13);
        $this->assertDatabaseCount('Allergeen', 5);
        $this->assertDatabaseCount('Leverancier', 5);
        $this->assertDatabaseCount('Magazijn', 13);
        $this->assertDatabaseCount('ProductPerAllergeen', 12);
        $this->assertDatabaseCount('ProductPerLeverancier', 17);
    }

    public function test_winegums_have_null_stock_and_kruis_drop_has_no_next_delivery(): void
    {
        $this->assertDatabaseHas('Magazijn', [
            'Id' => 10,
            'ProductId' => 10,
            'AantalAanwezig' => null,
        ]);

        $this->assertDatabaseHas('ProductPerLeverancier', [
            'Id' => 16,
            'LeverancierId' => 5,
            'ProductId' => 12,
            'Aantal' => 45,
            'DatumEerstVolgendeLevering' => null,
        ]);

        $this->assertDatabaseHas('ProductPerLeverancier', [
            'Id' => 17,
            'LeverancierId' => 5,
            'ProductId' => 13,
            'Aantal' => 23,
            'DatumEerstVolgendeLevering' => null,
        ]);
    }

    public function test_foreign_keys_point_to_product_allergeen_and_leverancier(): void
    {
        $this->assertTrue(Schema::hasForeignKey('Magazijn', ['ProductId']));
        $this->assertTrue(Schema::hasForeignKey('ProductPerAllergeen', ['ProductId']));
        $this->assertTrue(Schema::hasForeignKey('ProductPerAllergeen', ['AllergeenId']));
        $this->assertTrue(Schema::hasForeignKey('ProductPerLeverancier', ['LeverancierId']));
        $this->assertTrue(Schema::hasForeignKey('ProductPerLeverancier', ['ProductId']));
    }
}
