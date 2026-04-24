<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <style>
        :root {
            --brand: #6a1f2b;
            --accent: #b76e4d;
            --ink: #221b18;
            --muted: #6d625d;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(183, 110, 77, .12), transparent 24%),
                radial-gradient(circle at bottom right, rgba(106, 31, 43, .08), transparent 28%),
                linear-gradient(135deg, #f7f1e8 0%, #efe0cf 100%);
            font-family: "Segoe UI", Inter, system-ui, sans-serif;
            color: var(--ink);
        }

        .card {
            width: min(480px, calc(100vw - 32px));
            background: #fffdf9;
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(34, 27, 24, .14);
        }

        .mark {
            width: 210px;
            margin-bottom: 18px;
        }

        .eyebrow {
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 38px;
            font-family: Georgia, "Times New Roman", serif;
        }

        p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.65;
        }

        label {
            display: block;
            margin-bottom: 14px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            margin-top: 6px;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #d9c8b7;
            font: inherit;
            background: #fffdfa;
        }

        button {
            width: 100%;
            padding: 13px 16px;
            border: 0;
            border-radius: 14px;
            background: var(--brand);
            color: white;
            font: inherit;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(106, 31, 43, .18);
        }

        .hint {
            margin-top: 18px;
            font-size: 13px;
            color: var(--muted);
            background: rgba(183, 110, 77, .12);
            padding: 12px 14px;
            border-radius: 14px;
            line-height: 1.55;
        }

        .error {
            margin-bottom: 16px;
            background: #f8d7da;
            color: #842029;
            padding: 12px 14px;
            border-radius: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('brand/logo.svg') }}" alt="Magazzino Scipioni" class="mark">
        <div class="eyebrow">Magazzino Scipioni</div>
        <h1>Scipioni Admin</h1>
        <p>Accesso staff per gestire il club riservato, gli eventi e le prenotazioni.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('admin.login.attempt') }}">
            @csrf
            <label>
                Email
                <input type="email" name="email" value="{{ old('email', 'admin@magazzinoscipioni.it') }}" required>
            </label>
            <label>
                Password
                <input type="password" name="password" value="password" required>
            </label>
            <label style="display:flex;align-items:center;gap:10px;margin:18px 0;">
                <input type="checkbox" name="remember" value="1" style="width:auto;margin:0;">
                Ricordami
            </label>
            <button type="submit">Accedi</button>
        </form>

        <div class="hint">
            Credenziali seed locali: <strong>admin@magazzinoscipioni.it</strong> / <strong>password</strong>
        </div>
    </div>
</body>
</html>
