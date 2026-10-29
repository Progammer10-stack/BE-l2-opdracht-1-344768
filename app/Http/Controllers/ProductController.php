<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        // Producten oplopend op barcode, inclusief de magazijnrij
        $producten = Product::with('magazijn')
            ->orderBy('Barcode')
            ->get();

        return view('products.index', compact('producten'));
    }

    public function show(Product $product): View
    {
        // Leveringen van oud naar nieuw, met de bijbehorende leverancier
        $leveringen = $product->leveringen()
            ->with('leverancier')
            ->get();

        $leverancier = null;
        $volgendeLevering = null;

        if ($leveringen->isNotEmpty()) {
            $leverancier = $leveringen->first()->leverancier;
            $volgendeLevering = $leveringen->last()->DatumEerstVolgendeLevering;
        }

        return view('products.show', compact(
            'product',
            'leverancier',
            'leveringen',
            'volgendeLevering',
        ));
    }

    public function allergenen(Product $product): View
    {
        $product->load('allergenen');

        return view('products.allergenen', compact('product'));
    }
}
