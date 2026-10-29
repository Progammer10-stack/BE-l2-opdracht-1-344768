<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_levering_gegevens');

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_levering_gegevens(IN p_leverancier_id INT, IN p_product_id INT)
            BEGIN
                SELECT
                    pr.Naam AS ProductNaam,
                    l.Naam AS LeverancierNaam,
                    l.ContactPersoon,
                    l.Mobiel,
                    CAST(pr.IsActief AS UNSIGNED) AS IsActief
                FROM Leverancier AS l
                INNER JOIN ProductPerLeverancier AS ppl
                    ON ppl.LeverancierId = l.Id
                    AND ppl.ProductId = p_product_id
                INNER JOIN Product AS pr ON pr.Id = p_product_id
                WHERE l.Id = p_leverancier_id
                LIMIT 1;
            END
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_levering_gegevens');

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_levering_gegevens(IN p_leverancier_id INT, IN p_product_id INT)
            BEGIN
                SELECT
                    pr.Naam AS ProductNaam,
                    l.Naam AS LeverancierNaam,
                    l.ContactPersoon,
                    l.Mobiel
                FROM Leverancier AS l
                INNER JOIN ProductPerLeverancier AS ppl
                    ON ppl.LeverancierId = l.Id
                    AND ppl.ProductId = p_product_id
                INNER JOIN Product AS pr ON pr.Id = p_product_id
                WHERE l.Id = p_leverancier_id
                LIMIT 1;
            END
        SQL);
    }
};
