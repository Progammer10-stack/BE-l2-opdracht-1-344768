<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
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
    <h1>Inloggen</h1>

    @if ($errors->any())
        <p class="fout">Het e-mailadres of wachtwoord is niet juist.</p>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">E-mailadres</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Wachtwoord</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Inloggen</button>
    </form>

    <p>Testaccount: magazijn@example.com / password</p>
</body>
</html>
