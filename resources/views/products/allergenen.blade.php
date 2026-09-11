<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht Allergenen</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #cccccc; padding: 12px; text-align: left; }
        th { background: #f3f4f6; }
        .terug { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Overzicht Allergenen</h1>

    <p><strong>Naam Product:</strong> {{ $product->Naam }}</p>
    <p><strong>Barcode:</strong> {{ $product->Barcode }}</p>

    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Omschrijving</th>
            </tr>
        </thead>
        <tbody>
            @if ($product->allergenen->isEmpty())
                <tr>
                    <td colspan="2">
                        In dit product zitten geen stoffen die een allergische reactie kunnen veroorzaken
                    </td>
                </tr>
            @else
                @foreach ($product->allergenen as $allergeen)
                    <tr>
                        <td>{{ $allergeen->Naam }}</td>
                        <td>{{ $allergeen->Omschrijving }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    @if ($product->allergenen->isEmpty())
        <p>Je gaat over vier seconden terug naar Overzicht Magazijn Jamin.</p>

        <script>
            setTimeout(function () {
                window.location.href = "{{ route('products.index') }}";
            }, 4000);
        </script>
    @endif

    <a class="terug" href="{{ route('products.index') }}">Terug naar Overzicht Magazijn Jamin</a>
</body>
</html>
