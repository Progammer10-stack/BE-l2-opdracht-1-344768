<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht Magazijn Jamin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #cccccc; padding: 12px; text-align: left; }
        th { background: #f3f4f6; }
        .info-knop {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            font-weight: bold;
            text-decoration: none;
        }
        .allergenen-knop { color: #dc2626; font-size: 24px; font-weight: bold; text-decoration: none; }
        button { padding: 8px 14px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Overzicht Magazijn Jamin</h1>
            <p>Producten in het magazijn</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Uitloggen</button>
        </form>
    </header>

    <table>
        <thead>
            <tr>
                <th>Barcode</th>
                <th>Naam product</th>
                <th>Verpakkingseenheid</th>
                <th>Aantal aanwezig</th>
                <th>Allergenen Info</th>
                <th>Leverantie Info</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($producten as $product)
                <tr>
                    <td>{{ $product->Barcode }}</td>
                    <td>{{ $product->Naam }}</td>
                    <td>{{ $product->magazijn?->VerpakkingsEenheidInKilogram }} kg</td>
                    <td>{{ $product->magazijn?->AantalAanwezig ?? 'NULL' }}</td>
                    <td>
                        <a
                            class="allergenen-knop"
                            href="{{ route('products.allergenen', $product) }}"
                            title="Bekijk allergeneninformatie"
                            aria-label="Bekijk allergeneninformatie van {{ $product->Naam }}"
                        >×</a>
                    </td>
                    <td>
                        <a
                            class="info-knop"
                            href="{{ route('products.show', $product) }}"
                            title="Bekijk leverantie-informatie"
                            aria-label="Bekijk leverantie-informatie van {{ $product->Naam }}"
                        >?</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
