<?php

namespace App\Http\Controllers;

use App\Database\LeverancierGegevens;
use Illuminate\View\View;

class LeverancierController extends Controller
{
    public function index(): View
    {
        $leveranciers = (new LeverancierGegevens)->overzicht();

        return view('leveranciers.index', [
            'leveranciers' => $leveranciers,
        ]);
    }

    public function producten(int $leverancier): View
    {
        $gegevens = (new LeverancierGegevens)->geleverdeProducten($leverancier);

        if ($gegevens === null) {
            abort(404);
        }

        return view('leveranciers.producten', $gegevens);
    }
}
