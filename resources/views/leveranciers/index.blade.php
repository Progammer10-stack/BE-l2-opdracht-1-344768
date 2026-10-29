<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht leveranciers</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #cccccc; padding: 12px; text-align: left; }
        th { background: #f3f4f6; }
        .doos {
            display: inline-block;
            width: 22px;
            height: 22px;
            border: 2px solid #111111;
            text-decoration: none;
        }
        .home { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Overzicht leveranciers</h1>

    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Contactpersoon</th>
                <th>Leveranciernummer</th>
                <th>Mobiel</th>
                <th>Aantal verschillende producten</th>
                <th>Toon producten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leveranciers as $leverancier)
                <tr>
                    <td>{{ $leverancier['Naam'] }}</td>
                    <td>{{ $leverancier['ContactPersoon'] }}</td>
                    <td>{{ $leverancier['LeverancierNummer'] }}</td>
                    <td>{{ $leverancier['Mobiel'] }}</td>
                    <td>{{ $leverancier['AantalVerschillendeProducten'] }}</td>
                    <td>
                        <a
                            class="doos"
                            href="{{ route('leveranciers.producten', $leverancier['Id']) }}"
                            title="Toon producten"
                            aria-label="Toon producten van {{ $leverancier['Naam'] }}"
                        ></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a class="home" href="{{ route('home') }}">Home</a>
</body>
</html>
