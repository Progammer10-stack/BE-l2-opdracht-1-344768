<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leverantie-informatie</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #cccccc; padding: 12px; text-align: left; }
        th { background: #f3f4f6; }
        .terug { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Leverantie-informatie van {{ $product->Naam }}</h1>

    <h2>Leverancier</h2>

    @if ($leverancier)
        <p><strong>Naam:</strong> {{ $leverancier->Naam }}</p>
        <p><strong>Contactpersoon:</strong> {{ $leverancier->ContactPersoon }}</p>
        <p><strong>Leveranciernummer:</strong> {{ $leverancier->LeverancierNummer }}</p>
        <p><strong>Mobiel nummer:</strong> {{ $leverancier->Mobiel }}</p>
    @endif

    <h2>Productinformatie</h2>

    <table>
        <thead>
            <tr>
                <th>Naam product</th>
                <th>Datum levering</th>
                <th>Aantal</th>
                <th>Eerstvolgende levering</th>
            </tr>
        </thead>
        <tbody>
            @if (is_null($product->magazijn?->AantalAanwezig))
                <tr>
                    <td colspan="4">
                        Er is van dit product op dit moment geen voorraad aanwezig, de verwachte eerstvolgende levering is: {{ $volgendeLevering ? $volgendeLevering->format('d-m-Y') : 'onbekend' }}
                    </td>
                </tr>
            @else
                @forelse ($leveringen as $levering)
                    <tr>
                        <td>{{ $product->Naam }}</td>
                        <td>{{ $levering->DatumLevering?->format('d-m-Y') ?? 'onbekend' }}</td>
                        <td>{{ $levering->Aantal }}</td>
                        <td>{{ $levering->DatumEerstVolgendeLevering?->format('d-m-Y') ?? 'onbekend' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>{{ $product->Naam }}</td>
                        <td>onbekend</td>
                        <td>onbekend</td>
                        <td>onbekend</td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </table>

    <a class="terug" href="{{ route('products.index') }}">Terug naar magazijnoverzicht</a>
</body>
</html>
