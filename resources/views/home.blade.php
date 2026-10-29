<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magazijn Jamin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 720px; margin: 60px auto; padding: 20px; }
        p { color: #374151; }
        .keuzes { display: flex; gap: 16px; margin-top: 28px; }
        .keuze {
            flex: 1;
            display: block;
            padding: 24px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
        }
        .keuze:hover { border-color: #2563eb; }
        .keuze h2 { margin: 0 0 8px; font-size: 20px; }
        .keuze p { margin: 0; }
    </style>
</head>
<body>
    <h1>Magazijn Jamin</h1>

    @can('access-medewerker-dashboard')
        <p>Kies een overzicht.</p>

        <div class="keuzes">
            <a class="keuze" href="{{ route('leveranciers.index') }}">
                <h2>Overzicht Leveranciers</h2>
                <p>Bekijk de leveranciers en hun producten.</p>
            </a>

            <a class="keuze" href="{{ route('products.index') }}">
                <h2>Overzicht producten Jamin</h2>
                <p>Bekijk de producten in het magazijn.</p>
            </a>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Uitloggen</button>
        </form>
    @else
        <p>Kies of je wilt inloggen of een account wilt aanmaken.</p>

        <div class="keuzes">
            <a class="keuze" href="{{ route('login') }}">
                <h2>Inloggen</h2>
                <p>Ga verder met een bestaand account.</p>
            </a>

            <a class="keuze" href="{{ route('register') }}">
                <h2>Registreren</h2>
                <p>Maak een nieuw account aan.</p>
            </a>
        </div>
    @endcan
</body>
</html>
