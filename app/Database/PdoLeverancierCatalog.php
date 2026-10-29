<?php

namespace App\Database;

use App\Contracts\LeverancierCatalog;
use PDO;

class PdoLeverancierCatalog implements LeverancierCatalog
{
    public function __construct(private PDO $pdo) {}

    public function overzicht(): array
    {
        $statement = $this->pdo->query('CALL sp_leverancier_overzicht()');
        $leveranciers = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $leveranciers;
    }

    public function geleverdeProducten(int $leverancierId): ?array
    {
        $leverancierStatement = $this->pdo->prepare('CALL sp_leverancier_gegevens(?)');
        $leverancierStatement->execute([$leverancierId]);
        $leverancier = $leverancierStatement->fetch(PDO::FETCH_ASSOC);
        $leverancierStatement->closeCursor();

        if ($leverancier === false) {
            return null;
        }

        $productStatement = $this->pdo->prepare('CALL sp_geleverde_producten(?)');
        $productStatement->execute([$leverancierId]);
        $producten = $productStatement->fetchAll(PDO::FETCH_ASSOC);
        $productStatement->closeCursor();

        return [
            'leverancier' => $leverancier,
            'producten' => $producten,
        ];
    }

    public function leveringGegevens(int $leverancierId, int $productId): ?array
    {
        $statement = $this->pdo->prepare('CALL sp_levering_gegevens(?, ?)');
        $statement->execute([$leverancierId, $productId]);
        $gegevens = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        if ($gegevens === false) {
            return null;
        }

        return $gegevens;
    }

    public function registreerLevering(
        int $leverancierId,
        int $productId,
        int $aantal,
        string $datumLevering,
        string $datumEerstVolgendeLevering,
    ): void {
        $statement = $this->pdo->prepare('CALL sp_registreer_levering(?, ?, ?, ?, ?)');
        $statement->execute([
            $leverancierId,
            $productId,
            $aantal,
            $datumLevering,
            $datumEerstVolgendeLevering,
        ]);
        $statement->closeCursor();
    }
}
