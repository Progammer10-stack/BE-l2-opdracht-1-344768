<?php

namespace App\Http\Controllers;

use App\Database\LeverancierGegevens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeveringController extends Controller
{
    public function create(int $leverancier, int $product): View
    {
        $gegevens = (new LeverancierGegevens)->leveringGegevens($leverancier, $product);

        if ($gegevens === null) {
            abort(404);
        }

        return view('leveranciers.levering', [
            'leverancierId' => $leverancier,
            'productId' => $product,
            'gegevens' => $gegevens,
        ]);
    }

    public function store(Request $request, int $leverancier, int $product): View|RedirectResponse
    {
        $leverancierGegevens = new LeverancierGegevens;
        $gegevens = $leverancierGegevens->leveringGegevens($leverancier, $product);

        if ($gegevens === null) {
            abort(404);
        }

        $gecontroleerd = $request->validate([
            'aantal' => ['required', 'integer', 'min:1'],
            'datum_eerstvolgende_levering' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'aantal.required' => 'Vul het aantal producteenheden in.',
            'aantal.integer' => 'Het aantal producteenheden moet een geheel getal zijn.',
            'aantal.min' => 'Het aantal producteenheden moet minstens 1 zijn.',
            'datum_eerstvolgende_levering.required' => 'Vul de datum van de eerstvolgende levering in.',
            'datum_eerstvolgende_levering.date' => 'De datum van de eerstvolgende levering is ongeldig.',
            'datum_eerstvolgende_levering.after_or_equal' => 'De datum van de eerstvolgende levering mag niet in het verleden liggen.',
        ]);

        // IsActief = 0 betekent: dit product wordt niet meer gemaakt. Dan slaan we niets op.
        if (! $this->productWordtNogGemaakt($gegevens['IsActief'])) {
            return view('leveranciers.levering', [
                'leverancierId' => $leverancier,
                'productId' => $product,
                'gegevens' => $gegevens,
                'nietGeproduceerd' => true,
            ]);
        }

        $leverancierGegevens->registreerLevering(
            $leverancier,
            $product,
            (int) $gecontroleerd['aantal'],
            now()->toDateString(),
            $gecontroleerd['datum_eerstvolgende_levering'],
        );

        return redirect()->route('leveranciers.producten', $leverancier);
    }

    private function productWordtNogGemaakt(mixed $isActief): bool
    {
        // MySQL BIT komt via PDO soms binnen als het teken met code 0 of 1, niet als het cijfer "0" of "1".
        if (is_string($isActief) && strlen($isActief) === 1 && ! ctype_digit($isActief)) {
            return ord($isActief) === 1;
        }

        return (int) $isActief === 1;
    }
}
