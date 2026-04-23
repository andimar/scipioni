@php
    $title = 'Eventi Admin';
    $heading = 'Eventi';
    $subtitle = 'Elenco operativo degli eventi del club, con categoria, stato e volume prenotazioni.';
@endphp

@extends('admin.layouts.app')

@section('content')
    <div class="panel hero-panel" style="margin-bottom:22px;">
        <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0 0 8px;font-family:Georgia, 'Times New Roman', serif;font-size:28px;">Catalogo operativo eventi</h2>
                <p class="table-subtitle">Vista staff pensata per controllare rapidamente stato pubblicazione, prezzo, data e volume prenotazioni senza dover entrare ancora nel dettaglio.</p>
            </div>
            <div class="actions">
                <div class="user-chip">
                    <span>Totale righe</span>
                    <strong>{{ $events->total() }}</strong>
                </div>
                <a href="{{ route('admin.events.create') }}" class="button">Nuovo evento</a>
            </div>
        </div>
    </div>

    <div class="panel table-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap;">
            <div>
                <h2 class="table-title">Elenco eventi</h2>
                <p class="table-subtitle">Base solida per il prossimo step: CRUD completo, filtri, modifica e gestione prenotazioni per singolo evento.</p>
            </div>
            <div class="actions">
                <span class="badge brand">bozza / pubblicato</span>
                <span class="badge ok">monitoraggio base</span>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Categoria</th>
                        <th>Data</th>
                        <th>Prezzo</th>
                        <th>Stato</th>
                        <th>Prenotazioni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ $event->title }}</div>
                                <div class="muted">{{ $event->subtitle }}</div>
                            </td>
                            <td>{{ $event->category?->name ?? 'N/D' }}</td>
                            <td>{{ optional($event->starts_at)?->format('d/m/Y H:i') ?? 'N/D' }}</td>
                            <td>{{ number_format((float) $event->price, 2, ',', '.') }} euro</td>
                            <td>
                                <span class="badge {{ $event->status === 'published' ? 'ok' : 'brand' }}">
                                    {{ $event->status }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $event->bookings_count }}</div>
                                <a href="{{ route('admin.events.edit', $event) }}" style="font-size:12px;color:#6a1f2b;font-weight:700;">Modifica evento</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Nessun evento disponibile.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $events->links() }}
        </div>
    </div>
@endsection
