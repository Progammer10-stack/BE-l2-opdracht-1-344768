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

        $this->dropProcedures();

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

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_registreer_levering(
                IN p_leverancier_id INT,
                IN p_product_id INT,
                IN p_aantal INT,
                IN p_datum_levering DATE,
                IN p_datum_eerstvolgende DATE
            )
            BEGIN
                INSERT INTO ProductPerLeverancier (
                    LeverancierId,
                    ProductId,
                    DatumLevering,
                    Aantal,
                    DatumEerstVolgendeLevering
                ) VALUES (
                    p_leverancier_id,
                    p_product_id,
                    p_datum_levering,
                    p_aantal,
                    p_datum_eerstvolgende
                );

                UPDATE Magazijn
                SET AantalAanwezig = IFNULL(AantalAanwezig, 0) + p_aantal
                WHERE ProductId = p_product_id;
            END
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropProcedures();
    }

    private function dropProcedures(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_levering_gegevens');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_registreer_levering');
    }
};
