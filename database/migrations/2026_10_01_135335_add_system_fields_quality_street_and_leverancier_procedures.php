<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'Product',
        'Allergeen',
        'Leverancier',
        'Magazijn',
        'ProductPerAllergeen',
        'ProductPerLeverancier',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            $this->addSystemFields($table);
        }

        foreach (['DatumLevering', 'DatumEerstVolgendeLevering'] as $column) {
            DB::table('ProductPerLeverancier')
                ->where($column, 'like', '2024-10-%')
                ->update([
                    $column => DB::raw("REPLACE($column, '2024-10-', '2024-11-')"),
                ]);
        }

        DB::table('Product')->where('Id', 10)->update(['IsActief' => 0]);

        if (! DB::table('Leverancier')->where('Id', 6)->exists()) {
            DB::table('Leverancier')->insert([
                'Id' => 6,
                'Naam' => 'Quality Street',
                'ContactPersoon' => 'Johan Nooij',
                'LeverancierNummer' => 'L1029234586',
                'Mobiel' => '06-23458456',
            ]);
        }

        $this->createProcedures();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropProcedures();

        DB::table('Leverancier')->where('Id', 6)->delete();
        DB::table('Product')->where('Id', 10)->update(['IsActief' => 1]);

        foreach (['DatumLevering', 'DatumEerstVolgendeLevering'] as $column) {
            DB::table('ProductPerLeverancier')
                ->where($column, 'like', '2024-11-%')
                ->update([
                    $column => DB::raw("REPLACE($column, '2024-11-', '2024-10-')"),
                ]);
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'IsActief')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn([
                    'IsActief',
                    'Opmerking',
                    'DatumAangemaakt',
                    'DatumGewijzigd',
                ]);
            });
        }
    }

    private function addSystemFields(string $table): void
    {
        if (Schema::hasColumn($table, 'IsActief')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `$table` ADD IsActief BIT NOT NULL DEFAULT 1");
            DB::statement("ALTER TABLE `$table` ADD Opmerking VARCHAR(255) NULL");
            DB::statement("ALTER TABLE `$table` ADD DatumAangemaakt DATETIME(6) NOT NULL DEFAULT NOW(6)");
            DB::statement("ALTER TABLE `$table` ADD DatumGewijzigd DATETIME(6) NOT NULL DEFAULT NOW(6) ON UPDATE NOW(6)");

            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->boolean('IsActief')->default(true);
            $blueprint->string('Opmerking', 255)->nullable();
            $blueprint->dateTime('DatumAangemaakt')->nullable();
            $blueprint->dateTime('DatumGewijzigd')->nullable();
        });
    }

    private function createProcedures(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->dropProcedures();

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_leverancier_overzicht()
            BEGIN
                SELECT
                    l.Id,
                    l.Naam,
                    l.ContactPersoon,
                    l.LeverancierNummer,
                    l.Mobiel,
                    COUNT(DISTINCT ppl.ProductId) AS AantalVerschillendeProducten
                FROM Leverancier AS l
                LEFT JOIN ProductPerLeverancier AS ppl ON ppl.LeverancierId = l.Id
                GROUP BY l.Id, l.Naam, l.ContactPersoon, l.LeverancierNummer, l.Mobiel
                ORDER BY AantalVerschillendeProducten DESC, l.Naam ASC;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_leverancier_gegevens(IN p_leverancier_id INT)
            BEGIN
                SELECT Id, Naam, ContactPersoon, LeverancierNummer, Mobiel
                FROM Leverancier
                WHERE Id = p_leverancier_id;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_geleverde_producten(IN p_leverancier_id INT)
            BEGIN
                SELECT
                    pr.Id AS ProductId,
                    pr.Naam AS ProductNaam,
                    m.AantalAanwezig,
                    m.VerpakkingsEenheidInKilogram,
                    MAX(ppl.DatumLevering) AS LaatsteLevering
                FROM ProductPerLeverancier AS ppl
                INNER JOIN Product AS pr ON pr.Id = ppl.ProductId
                LEFT JOIN Magazijn AS m ON m.ProductId = pr.Id
                WHERE ppl.LeverancierId = p_leverancier_id
                GROUP BY pr.Id, pr.Naam, m.AantalAanwezig, m.VerpakkingsEenheidInKilogram
                ORDER BY (m.AantalAanwezig IS NULL), m.AantalAanwezig DESC, pr.Naam ASC;
            END
        SQL);
    }

    private function dropProcedures(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_leverancier_overzicht');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_leverancier_gegevens');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_geleverde_producten');
    }
};
