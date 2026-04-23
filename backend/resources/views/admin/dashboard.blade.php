@php
    $title = 'Dashboard Admin';
    $heading = 'Dashboard';
    $subtitle = 'Panoramica rapida su utenti, eventi e prenotazioni del club.';
@endphp

@extends('admin.layouts.app')

@section('content')
    <div class="panel hero-panel" style="margin-bottom:22px;">
        <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0 0 8px;font-family:Georgia, 'Times New Roman', serif;font-size:28px;">Cabina di regia del club</h2>
                <p class="table-subtitle">Qui il team puo' verificare in pochi secondi lo stato del catalogo eventi, il volume prenotazioni e la base utenti gia' profilata.</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="button">Apri gestione eventi</a>
        </div>
    </div>

    <div class="grid" style="margin-bottom:22px;">
        <div class="panel stat-card">
            <div class="stat-label">Utenti registrati</div>
            <div class="stat-value">{{ $stats['users'] }}</div>
            <div class="stat-foot">Base clienti disponibile per segmentazione e inviti mirati.</div>
        </div>
        <div class="panel stat-card">
            <div class="stat-label">Eventi totali</div>
            <div class="stat-value">{{ $stats['events'] }}</div>
            <div class="stat-foot">Contenuti pronti per essere ordinati, pubblicati o archiviati.</div>
        </div>
        <div class="panel stat-card">
            <div class="stat-label">Eventi pubblicati</div>
            <div class="stat-value">{{ $stats['published_events'] }}</div>
            <div class="stat-foot">Esperienze visibili agli utenti nell'area riservata.</div>
        </div>
        <div class="panel stat-card">
            <div class="stat-label">Prenotazioni</div>
            <div class="stat-value">{{ $stats['bookings'] }}</div>
            <div class="stat-foot">Indicatore rapido del coinvolgimento sul calendario in corso.</div>
        </div>
    </div>

    <div class="panel table-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;">
            <div>
                <h2 class="table-title">Eventi recenti</h2>
                <div class="table-subtitle">Prime entita' utili per verificare lo stato del catalogo, il seed e la leggibilita' operativa del pannello.</div>
            </div>
            <a href="{{ route('admin.events.index') }}" class="button alt">Lista completa</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Categoria</th>
                        <th>Data</th>
                        <th>Stato</th>
                        <th>Prenotazioni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentEvents as $event)
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ $event->title }}</div>
                                <div class="muted">{{ $event->subtitle }}</div>
                            </td>
                            <td>{{ $event->category?->name ?? 'N/D' }}</td>
                            <td>{{ optional($event->starts_at)?->format('d/m/Y H:i') ?? 'N/D' }}</td>
                            <td>
                                <span class="badge {{ $event->status === 'published' ? 'ok' : 'brand' }}">
                                    {{ $event->status }}
                                </span>
                            </td>
                            <td>{{ $event->bookings_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Nessun evento disponibile.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
