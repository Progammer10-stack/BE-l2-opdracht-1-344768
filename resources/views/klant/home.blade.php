<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klantomgeving</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 640px; margin: 60px auto; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        p { color: #374151; }
        button { padding: 8px 14px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <h1>Klantomgeving</h1>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Uitloggen</button>
        </form>
    </header>

    <p>Je account heeft de rol klant. De magazijngegevens zijn voor jou niet zichtbaar.</p>
</body>
</html>
