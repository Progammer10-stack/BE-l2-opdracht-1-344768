<?php

namespace App\Contracts;

interface LeverancierCatalog
{
    /**
     * @return list<array{
     *     Id: int|string,
     *     Naam: string,
     *     ContactPersoon: string,
     *     LeverancierNummer: string,
     *     Mobiel: string,
     *     AantalVerschillendeProducten: int|string
     * }>
     */
    public function overzicht(): array;

    /**
     * @return array{
     *     leverancier: array{
     *         Id: int|string,
     *         Naam: string,
     *         ContactPersoon: string,
     *         LeverancierNummer: string,
     *         Mobiel: string
     *     },
     *     producten: list<array{
     *         ProductId: int|string,
     *         ProductNaam: string,
     *         AantalAanwezig: int|string|null,
     *         VerpakkingsEenheidInKilogram: int|string,
     *         LaatsteLevering: string|null
     *     }>
     * }|null
     */
    public function geleverdeProducten(int $leverancierId): ?array;

    /**
     * @return array{
     *     ProductNaam: string,
     *     LeverancierNaam: string,
     *     ContactPersoon: string,
     *     Mobiel: string,
     *     IsActief: int|string
     * }|null
     */
    public function leveringGegevens(int $leverancierId, int $productId): ?array;

    public function registreerLevering(
        int $leverancierId,
        int $productId,
        int $aantal,
        string $datumLevering,
        string $datumEerstVolgendeLevering,
    ): void;
}
