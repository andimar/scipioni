<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Scipioni Admin' }}</title>
    <style>
        :root {
            --bg: #f3eadf;
            --panel: #fffdf9;
            --ink: #221b18;
            --muted: #6d625d;
            --line: #dfd2c2;
            --brand: #6a1f2b;
            --accent: #b76e4d;
            --ok: #2f7d4a;
            --warning: #8c5b1d;
            --shadow: 0 18px 48px rgba(34, 27, 24, .10);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Inter, system-ui, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(183, 110, 77, .12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(106, 31, 43, .08), transparent 26%),
                linear-gradient(135deg, #f7f1e8 0%, #efe0cf 100%);
            color: var(--ink);
        }

        a { color: inherit; text-decoration: none; }

        .shell {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .03), rgba(255, 255, 255, 0) 24%),
                linear-gradient(180deg, #1f1815 0%, #281d18 100%);
            color: #f6e8d8;
            padding: 30px 24px;
        }

        .brand-mark {
            width: 190px;
            margin-bottom: 18px;
            filter: invert(1);
        }

        .brand {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 30px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 10px;
        }

        .brand-sub {
            color: #d9c1aa;
            font-size: 14px;
            line-height: 1.55;
            margin-bottom: 28px;
            max-width: 220px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 14px;
            margin-bottom: 10px;
            border-radius: 14px;
            color: #f2ddca;
            transition: background .16s ease, transform .16s ease;
        }

        .nav a.active,
        .nav a:hover {
            background: rgba(183, 110, 77, .18);
            transform: translateX(2px);
        }

        .nav-meta {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, .08);
            color: #ccb39c;
            font-size: 13px;
            line-height: 1.6;
        }

        .content { padding: 28px 30px; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(106, 31, 43, .08);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .hero-panel {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .72), rgba(255, 247, 239, .95)),
                linear-gradient(135deg, rgba(106, 31, 43, .06), rgba(183, 110, 77, .04));
        }

        .grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255, 255, 255, .92), rgba(255, 250, 244, .96));
        }

        .stat-card::after {
            content: "";
            position: absolute;
            inset: auto -28px -32px auto;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(183, 110, 77, .09);
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .stat-value {
            font-size: 38px;
            font-weight: 700;
            font-family: Georgia, "Times New Roman", serif;
            margin-bottom: 8px;
        }

        .stat-foot {
            color: var(--muted);
            font-size: 13px;
            position: relative;
            z-index: 1;
        }

        .table-card { overflow: hidden; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th,
        td {
            text-align: left;
            padding: 16px 10px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .badge {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #efe0cf;
        }

        .badge.ok { background: rgba(47, 125, 74, .14); color: var(--ok); }
        .badge.brand { background: rgba(106, 31, 43, .12); color: var(--brand); }
        .badge.warning { background: rgba(140, 91, 29, .12); color: var(--warning); }

        .button,
        button {
            border: 0;
            border-radius: 14px;
            background: var(--brand);
            color: white;
            padding: 11px 16px;
            font: inherit;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(106, 31, 43, .18);
        }

        .button.alt {
            background: transparent;
            color: var(--brand);
            border: 1px solid rgba(106, 31, 43, .18);
            box-shadow: none;
        }

        .muted { color: var(--muted); }

        .eyebrow {
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .section-title {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            margin: 0 0 8px;
            line-height: 1.05;
        }

        .section-subtitle {
            color: var(--muted);
            margin: 0;
            max-width: 720px;
            line-height: 1.6;
        }

        .title-stack { min-width: 0; }
        .logout-form { margin: 0; }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .75);
            border: 1px solid rgba(106, 31, 43, .08);
            color: var(--muted);
        }

        .user-chip strong { color: var(--ink); }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-title {
            margin: 0 0 6px;
            font-size: 24px;
            font-family: Georgia, "Times New Roman", serif;
        }

        .table-subtitle {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .pagination > * {
            min-width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            background: white;
            padding: 0 12px;
        }

        .pagination .active > span {
            background: var(--brand);
            color: white;
            border-color: transparent;
        }

        .pagination .disabled > span { color: #b7aaa1; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field.full { grid-column: 1 / -1; }

        .field label {
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fffdfa;
            color: var(--ink);
            padding: 13px 14px;
            font: inherit;
        }

        .field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .checkbox-card {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            background: #fffdfa;
        }

        .checkbox-card input {
            width: auto;
            margin: 0;
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(47, 125, 74, .18);
            background: rgba(47, 125, 74, .08);
            color: var(--ok);
        }

        .error-list {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(140, 29, 40, .16);
            background: rgba(140, 29, 40, .08);
            color: #7a2230;
        }

        .stack {
            display: grid;
            gap: 18px;
        }

        @media (max-width: 920px) {
            .shell { grid-template-columns: 1fr; }
            .content { padding: 20px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <img src="{{ asset('brand/logo.svg') }}" alt="Magazzino Scipioni" class="brand-mark">
        <div class="brand">Scipioni<br>Admin</div>
        <div class="brand-sub">Pannello staff per eventi, prenotazioni e utenti del club riservato.</div>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">Eventi</a>
            <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">Prenotazioni</a>
        </nav>
        <div class="nav-meta">
            Accesso staff per controllare catalogo, disponibilita' e flusso prenotazioni del club.
        </div>
    </aside>
    <main class="content">
        <div class="topbar">
            <div class="title-stack">
                <div class="eyebrow">Magazzino Scipioni</div>
                <h1 class="section-title">{{ $heading ?? 'Admin' }}</h1>
                @isset($subtitle)
                    <p class="section-subtitle">{{ $subtitle }}</p>
                @endisset
            </div>
            <div class="actions">
                <div class="user-chip">
                    <span>Connesso come</span>
                    <strong>{{ auth('admin')->user()?->name }}</strong>
                </div>
                <form method="post" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">Esci</button>
                </form>
            </div>
        </div>
        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error-list">
                {{ $errors->first() }}
            </div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
