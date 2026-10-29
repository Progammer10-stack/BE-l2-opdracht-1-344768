<?php

namespace App\Database;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class LeverancierGegevens
{
    /**
     * Leveranciers met het aantal verschillende producten.
     * Gesorteerd op dat aantal, van hoog naar laag, en daarna op naam.
     *
     * @return list<array<string, mixed>>
     */
    public function overzicht(): array
    {
        // Op MySQL hoort deze lijst uit de stored procedure te komen.
        if (DB::getDriverName() === 'mysql') {
            return $this->haalRijenUitProcedure('CALL sp_leverancier_overzicht()');
        }

        // SQLite (de tests) kan geen MySQL-procedures draaien.
        // Deze query doet hetzelfde als sp_leverancier_overzicht.
        $rijen = DB::table('Leverancier as l')
            ->leftJoin('ProductPerLeverancier as ppl', 'ppl.LeverancierId', '=', 'l.Id')
            ->groupBy('l.Id', 'l.Naam', 'l.ContactPersoon', 'l.LeverancierNummer', 'l.Mobiel')
            ->orderByRaw('COUNT(DISTINCT ppl.ProductId) DESC')
            ->orderBy('l.Naam')
            ->select([
                'l.Id',
                'l.Naam',
                'l.ContactPersoon',
                'l.LeverancierNummer',
                'l.Mobiel',
            ])
            ->selectRaw('COUNT(DISTINCT ppl.ProductId) as AantalVerschillendeProducten')
            ->get();

        return $this->objectenNaarArrays($rijen);
    }

    /**
     * Gegevens van één leverancier plus de producten die hij heeft geleverd.
     * null betekent: deze leverancier bestaat niet.
     *
     * @return array{leverancier: array<string, mixed>, producten: list<array<string, mixed>>}|null
     */
    public function geleverdeProducten(int $leverancierId): ?array
    {
        if (DB::getDriverName() === 'mysql') {
            $leverancier = $this->haalEersteRijUitProcedure(
                'CALL sp_leverancier_gegevens(?)',
                [$leverancierId],
            );

            if ($leverancier === null) {
                return null;
            }

            $producten = $this->haalRijenUitProcedure(
                'CALL sp_geleverde_producten(?)',
                [$leverancierId],
            );

            return [
                'leverancier' => $leverancier,
                'producten' => $this->maakProductregelsLeesbaar($producten),
            ];
        }

        $leverancier = DB::table('Leverancier')
            ->where('Id', $leverancierId)
            ->first(['Id', 'Naam', 'ContactPersoon', 'LeverancierNummer', 'Mobiel']);

        if ($leverancier === null) {
            return null;
        }

        // Zelfde sortering als sp_geleverde_producten:
        // voorraad van hoog naar laag, lege voorraad onderaan, daarna op naam.
        $producten = DB::table('ProductPerLeverancier as ppl')
            ->join('Product as pr', 'pr.Id', '=', 'ppl.ProductId')
            ->leftJoin('Magazijn as m', 'm.ProductId', '=', 'pr.Id')
            ->where('ppl.LeverancierId', $leverancierId)
            ->groupBy('pr.Id', 'pr.Naam', 'm.AantalAanwezig', 'm.VerpakkingsEenheidInKilogram')
            ->orderByRaw('m.AantalAanwezig IS NULL')
            ->orderByDesc('m.AantalAanwezig')
            ->orderBy('pr.Naam')
            ->select([
                'pr.Id as ProductId',
                'pr.Naam as ProductNaam',
                'm.AantalAanwezig',
                'm.VerpakkingsEenheidInKilogram',
            ])
            ->selectRaw('MAX(ppl.DatumLevering) as LaatsteLevering')
            ->get();

        return [
            'leverancier' => (array) $leverancier,
            'producten' => $this->maakProductregelsLeesbaar($this->objectenNaarArrays($producten)),
        ];
    }

    /**
     * Gegevens voor het formulier Nieuwe levering.
     * null betekent: deze leverancier levert dit product niet.
     *
     * @return array<string, mixed>|null
     */
    public function leveringGegevens(int $leverancierId, int $productId): ?array
    {
        if (DB::getDriverName() === 'mysql') {
            return $this->haalEersteRijUitProcedure(
                'CALL sp_levering_gegevens(?, ?)',
                [$leverancierId, $productId],
            );
        }

        $gegevens = DB::table('Leverancier as l')
            ->join('ProductPerLeverancier as ppl', 'ppl.LeverancierId', '=', 'l.Id')
            ->join('Product as pr', 'pr.Id', '=', 'ppl.ProductId')
            ->where('l.Id', $leverancierId)
            ->where('pr.Id', $productId)
            ->first([
                'pr.Naam as ProductNaam',
                'l.Naam as LeverancierNaam',
                'l.ContactPersoon',
                'l.Mobiel',
                'pr.IsActief',
            ]);

        if ($gegevens === null) {
            return null;
        }

        return (array) $gegevens;
    }

    /**
     * Sla een nieuwe levering op en tel het aantal op bij de magazijnvoorraad.
     * Doet hetzelfde als sp_registreer_levering.
     */
    public function registreerLevering(
        int $leverancierId,
        int $productId,
        int $aantal,
        string $datumLevering,
        string $datumEerstVolgendeLevering,
    ): void {
        if (DB::getDriverName() === 'mysql') {
            $this->voerProcedureUit('CALL sp_registreer_levering(?, ?, ?, ?, ?)', [
                $leverancierId,
                $productId,
                $aantal,
                $datumLevering,
                $datumEerstVolgendeLevering,
            ]);

            return;
        }

        DB::beginTransaction();

        try {
            DB::table('ProductPerLeverancier')->insert([
                'LeverancierId' => $leverancierId,
                'ProductId' => $productId,
                'DatumLevering' => $datumLevering,
                'Aantal' => $aantal,
                'DatumEerstVolgendeLevering' => $datumEerstVolgendeLevering,
            ]);

            $aanwezig = DB::table('Magazijn')->where('ProductId', $productId)->value('AantalAanwezig');

            // NULL betekent: er is nog geen voorraad. Dan wordt de voorraad gelijk aan het geleverde aantal.
            $nieuwAantal = $aantal;

            if ($aanwezig !== null) {
                $nieuwAantal = (int) $aanwezig + $aantal;
            }

            DB::table('Magazijn')->where('ProductId', $productId)->update([
                'AantalAanwezig' => $nieuwAantal,
            ]);

            DB::commit();
        } catch (Throwable $fout) {
            DB::rollBack();

            throw $fout;
        }
    }

    /**
     * @param  list<mixed>  $parameters
     * @return list<array<string, mixed>>
     */
    private function haalRijenUitProcedure(string $sql, array $parameters = []): array
    {
        $statement = DB::connection()->getPdo()->prepare($sql);
        $statement->execute($parameters);

        $rijen = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Zonder closeCursor() kan de volgende procedure op dezelfde verbinding vastlopen.
        $statement->closeCursor();

        return $rijen;
    }

    /**
     * @param  list<mixed>  $parameters
     * @return array<string, mixed>|null
     */
    private function haalEersteRijUitProcedure(string $sql, array $parameters = []): ?array
    {
        $rijen = $this->haalRijenUitProcedure($sql, $parameters);

        if ($rijen === []) {
            return null;
        }

        return $rijen[0];
    }

    /**
     * @param  list<mixed>  $parameters
     */
    private function voerProcedureUit(string $sql, array $parameters): void
    {
        $statement = DB::connection()->getPdo()->prepare($sql);
        $statement->execute($parameters);
        $statement->closeCursor();
    }

    /**
     * @param  iterable<mixed>  $rijen
     * @return list<array<string, mixed>>
     */
    private function objectenNaarArrays(iterable $rijen): array
    {
        $resultaat = [];

        foreach ($rijen as $rij) {
            $resultaat[] = (array) $rij;
        }

        return $resultaat;
    }

    /**
     * Maak kilogram en datum klaar voor de tabel, bijvoorbeeld "2.5 kg" en "18-11-2024".
     *
     * @param  list<array<string, mixed>>  $producten
     * @return list<array<string, mixed>>
     */
    private function maakProductregelsLeesbaar(array $producten): array
    {
        $resultaat = [];

        foreach ($producten as $product) {
            $kilogram = number_format((float) $product['VerpakkingsEenheidInKilogram'], 2, '.', '');
            $kilogram = rtrim($kilogram, '0');
            $kilogram = rtrim($kilogram, '.');

            $product['Verpakking'] = $kilogram.' kg';

            if ($product['LaatsteLevering']) {
                $product['LaatsteLeveringTekst'] = Carbon::parse($product['LaatsteLevering'])->format('d-m-Y');
            } else {
                $product['LaatsteLeveringTekst'] = '';
            }

            $resultaat[] = $product;
        }

        return $resultaat;
    }
}
