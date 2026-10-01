<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 60px auto; padding: 20px; }
        label, input { display: block; width: 100%; box-sizing: border-box; }
        label { margin-top: 15px; }
        input { padding: 10px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 18px; cursor: pointer; }
        .fout { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Registreren</h1>

    @if ($errors->any())
        <ul class="fout">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label for="name">Naam</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">E-mailadres</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Wachtwoord</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Wachtwoord bevestigen</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Registreren</button>
    </form>

    <p><a href="{{ route('login') }}">Al een account? Inloggen</a></p>
</body>
</html>
