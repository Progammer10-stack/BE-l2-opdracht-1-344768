<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        // Producten oplopend op barcode, zoals in user story 2
        $producten = Product::with('magazijn')
            ->orderBy('Barcode')
            ->get();

        return view('products.index', compact('producten'));
    }

    public function show(Product $product): View
    {
        $leveringen = $product->leveringen()
            ->with('leverancier')
            ->get();

        $leverancier = $leveringen->first()?->leverancier;
        $volgendeLevering = $leveringen->last()?->DatumEerstVolgendeLevering;

        return view('products.show', compact(
            'product',
            'leverancier',
            'leveringen',
            'volgendeLevering',
        ));
    }

    public function allergenen(Product $product): View
    {
        // Allergenen oplopend op naam, zoals in user story 2
        $product->load(['allergenen' => function ($query) {
            $query->orderBy('Naam');
        }]);

        return view('products.allergenen', compact('product'));
    }
}
