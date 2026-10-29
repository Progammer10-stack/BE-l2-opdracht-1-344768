<?php

namespace App\Http\Controllers;

use App\Contracts\LeverancierCatalog;
use Illuminate\View\View;

class LeverancierController extends Controller
{
    public function __construct(private LeverancierCatalog $catalog) {}

    public function index(): View
    {
        return view('leveranciers.index', [
            'leveranciers' => $this->catalog->overzicht(),
        ]);
    }

    public function producten(int $leverancier): View
    {
        $gegevens = $this->catalog->geleverdeProducten($leverancier);

        if ($gegevens === null) {
            abort(404);
        }

        return view('leveranciers.producten', $gegevens);
    }
}
