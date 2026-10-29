<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Levering product</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #ffffff; color: #111111; }
        .veld { display: flex; align-items: center; gap: 24px; margin-top: 18px; }
        .veld label { width: 280px; }
        .veld input { width: 180px; padding: 8px; border: 1px solid #111111; background: #ffffff; color: #111111; }
        .fout { color: #b91c1c; margin: 6px 0 0 304px; }
        .acties { display: flex; justify-content: space-between; align-items: center; max-width: 640px; margin-top: 28px; }
        .acties a { margin-left: 24px; }
        button { padding: 8px 16px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Levering product</h1>

    <p><strong>Product:</strong> {{ $gegevens['ProductNaam'] }}</p>
    <p><strong>Leverancier:</strong> {{ $gegevens['LeverancierNaam'] }}</p>
    <p><strong>Contactpersoon:</strong> {{ $gegevens['ContactPersoon'] }}</p>
    <p><strong>Mobiel:</strong> {{ $gegevens['Mobiel'] }}</p>

    @if ($nietGeproduceerd ?? false)
        <p>Het product {{ $gegevens['ProductNaam'] }} van de leverancier {{ $gegevens['LeverancierNaam'] }} wordt niet meer geproduceerd</p>

        <script>
            setTimeout(function () {
                window.location.href = "{{ route('leveranciers.producten', $leverancierId) }}";
            }, 4000);
        </script>
    @endif

    <form method="POST" action="{{ route('leveranciers.levering.store', [$leverancierId, $productId]) }}">
        @csrf

        <div class="veld">
            <label for="aantal">Aantal producteenheden</label>
            <input id="aantal" name="aantal" type="number" min="1" step="1" value="{{ old('aantal') }}">
        </div>
        @error('aantal')
            <p class="fout">{{ $message }}</p>
        @enderror

        <div class="veld">
            <label for="datum_eerstvolgende_levering">Datum eerstvolgende levering</label>
            <input id="datum_eerstvolgende_levering" name="datum_eerstvolgende_levering" type="date" value="{{ old('datum_eerstvolgende_levering') }}">
        </div>
        @error('datum_eerstvolgende_levering')
            <p class="fout">{{ $message }}</p>
        @enderror

        <div class="acties">
            <button type="submit">Sla op</button>
            <span>
                <a href="{{ route('leveranciers.producten', $leverancierId) }}">Terug</a>
                <a href="{{ route('home') }}">Home</a>
            </span>
        </div>
    </form>
</body>
</html>
