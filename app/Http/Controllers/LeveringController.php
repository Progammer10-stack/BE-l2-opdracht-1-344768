<?php

namespace App\Http\Controllers;

use App\Contracts\LeverancierCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeveringController extends Controller
{
    public function __construct(private LeverancierCatalog $catalog) {}

    public function create(int $leverancier, int $product): View
    {
        $gegevens = $this->catalog->leveringGegevens($leverancier, $product);

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
        $gegevens = $this->catalog->leveringGegevens($leverancier, $product);

        if ($gegevens === null) {
            abort(404);
        }

        $validated = $request->validate([
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

        if (! $this->productWordtNogGemaakt($gegevens['IsActief'])) {
            return view('leveranciers.levering', [
                'leverancierId' => $leverancier,
                'productId' => $product,
                'gegevens' => $gegevens,
                'nietGeproduceerd' => true,
            ]);
        }

        $this->catalog->registreerLevering(
            $leverancier,
            $product,
            (int) $validated['aantal'],
            now()->toDateString(),
            $validated['datum_eerstvolgende_levering'],
        );

        return redirect()->route('leveranciers.producten', $leverancier);
    }

    private function productWordtNogGemaakt(mixed $isActief): bool
    {
        if (is_string($isActief) && strlen($isActief) === 1 && ! ctype_digit($isActief)) {
            return ord($isActief) === 1;
        }

        return (int) $isActief === 1;
    }
}
