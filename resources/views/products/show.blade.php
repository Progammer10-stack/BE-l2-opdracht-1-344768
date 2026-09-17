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
        .melding { padding: 20px; background: #fef3c7; border: 1px solid #f59e0b; }
        .terug { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Leverantie-informatie van {{ $product->Naam }}</h1>

    @if (is_null($product->magazijn?->AantalAanwezig))
        <p class="melding">
            Er is van dit product op dit moment geen voorraad aanwezig,
            de verwachte eerstvolgende levering is:
            {{ $volgendeLevering ? $volgendeLevering->format('d-m-Y') : 'onbekend' }}
        </p>

        <p>Je gaat over vier seconden terug naar het magazijnoverzicht.</p>

        <script>
            setTimeout(function () {
                window.location.href = "{{ route('products.index') }}";
            }, 4000);
        </script>
    @else
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
                    <th>Datum laatste levering</th>
                    <th>Aantal</th>
                    <th>Eerstvolgende levering</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $product->Naam }}</td>
                    <td>{{ $laatsteLevering?->DatumLevering?->format('d-m-Y') ?? 'onbekend' }}</td>
                    <td>{{ $laatsteLevering?->Aantal ?? 'onbekend' }}</td>
                    <td>{{ $volgendeLevering?->format('d-m-Y') ?? 'onbekend' }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <a class="terug" href="{{ route('products.index') }}">Terug naar magazijnoverzicht</a>
</body>
</html>
