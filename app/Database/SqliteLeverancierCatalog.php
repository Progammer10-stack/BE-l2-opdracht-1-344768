<?php

namespace App\Database;

use App\Contracts\LeverancierCatalog;
use Illuminate\Support\Facades\DB;

class SqliteLeverancierCatalog implements LeverancierCatalog
{
    public function overzicht(): array
    {
        return DB::table('Leverancier as l')
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
            ->get()
            ->map(fn (object $leverancier): array => (array) $leverancier)
            ->all();
    }

    public function geleverdeProducten(int $leverancierId): ?array
    {
        $leverancier = DB::table('Leverancier')
            ->where('Id', $leverancierId)
            ->first(['Id', 'Naam', 'ContactPersoon', 'LeverancierNummer', 'Mobiel']);

        if ($leverancier === null) {
            return null;
        }

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
            ->get()
            ->map(fn (object $product): array => (array) $product)
            ->all();

        return [
            'leverancier' => (array) $leverancier,
            'producten' => $producten,
        ];
    }

    public function leveringGegevens(int $leverancierId, int $productId): ?array
    {
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

    public function registreerLevering(
        int $leverancierId,
        int $productId,
        int $aantal,
        string $datumLevering,
        string $datumEerstVolgendeLevering,
    ): void {
        DB::transaction(function () use ($leverancierId, $productId, $aantal, $datumLevering, $datumEerstVolgendeLevering): void {
            DB::table('ProductPerLeverancier')->insert([
                'LeverancierId' => $leverancierId,
                'ProductId' => $productId,
                'DatumLevering' => $datumLevering,
                'Aantal' => $aantal,
                'DatumEerstVolgendeLevering' => $datumEerstVolgendeLevering,
            ]);

            $aanwezig = DB::table('Magazijn')->where('ProductId', $productId)->value('AantalAanwezig');

            DB::table('Magazijn')->where('ProductId', $productId)->update([
                'AantalAanwezig' => ($aanwezig === null ? 0 : (int) $aanwezig) + $aantal,
            ]);
        });
    }
}
