<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $bestaat = DB::table('ProductPerLeverancier')
            ->where('ProductId', 13)
            ->where('DatumLevering', '2024-10-12')
            ->exists();

        if ($bestaat) {
            return;
        }

        DB::table('ProductPerLeverancier')->insert([
            'Id' => 17,
            'LeverancierId' => 5,
            'ProductId' => 13,
            'DatumLevering' => '2024-10-12',
            'Aantal' => 23,
            'DatumEerstVolgendeLevering' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ProductPerLeverancier')
            ->where('Id', 17)
            ->where('ProductId', 13)
            ->delete();
    }
};
