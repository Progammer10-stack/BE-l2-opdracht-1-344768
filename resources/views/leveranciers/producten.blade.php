<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geleverde producten</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #ffffff; color: #111111; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #cccccc; padding: 12px; text-align: left; background: #ffffff; color: #111111; }
        th { background: #f3f4f6; }
        .acties { margin-top: 20px; text-align: right; }
        .acties a { margin-left: 16px; }
    </style>
</head>
<body>
    <h1>Geleverde producten</h1>

    <p><strong>Naam leverancier:</strong> {{ $leverancier['Naam'] }}</p>
    <p><strong>Contactpersoon:</strong> {{ $leverancier['ContactPersoon'] }}</p>
    <p><strong>Leveranciernr:</strong> {{ $leverancier['LeverancierNummer'] }}</p>
    <p><strong>Mobiel:</strong> {{ $leverancier['Mobiel'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Naam product</th>
                <th>Aantal in Magazijn</th>
                <th>Verpakkingseenheid</th>
                <th>Laatste levering</th>
                <th>Nieuwe levering</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($producten as $product)
                <tr>
                    <td>{{ $product['ProductNaam'] }}</td>
                    <td>{{ $product['AantalAanwezig'] ?? 'NULL' }}</td>
                    <td>{{ $product['Verpakking'] }}</td>
                    <td>{{ $product['LaatsteLeveringTekst'] }}</td>
                    <td>
                        <a href="{{ route('leveranciers.levering.create', [$leverancier['Id'], $product['ProductId']]) }}">+</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Dit bedrijf heeft tot nu toe geen producten geleverd aan Jamin</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($producten === [])
        <script>
            setTimeout(function () {
                window.location.href = "{{ route('leveranciers.index') }}";
            }, 3000);
        </script>
    @endif

    <p class="acties">
        <a href="{{ route('leveranciers.index') }}">Terug</a>
        <a href="{{ route('home') }}">Home</a>
    </p>
</body>
</html>
