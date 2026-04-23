@php
    $title = 'Prenotazioni Admin';
    $heading = 'Prenotazioni';
    $subtitle = 'Vista operativa per monitorare richieste, conferme e lista attesa del club.';
@endphp

@extends('admin.layouts.app')

@section('content')
    <div class="stack">
        <div class="panel hero-panel">
            <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 8px;font-family:Georgia, 'Times New Roman', serif;font-size:28px;">Flusso prenotazioni</h2>
                    <p class="table-subtitle">Panoramica staff su richieste utenti, eventi coinvolti e dati profilo minimi utili alla gestione.</p>
                </div>
                <div class="user-chip">
                    <span>Totale righe</span>
                    <strong>{{ $bookings->total() }}</strong>
                </div>
            </div>
        </div>

        <div class="panel">
            <form method="get" class="form-grid">
                <div class="field">
                    <label for="status">Stato</label>
                    <select id="status" name="status">
                        <option value="">Tutti</option>
                        @foreach (['requested' => 'Richiesta', 'confirmed' => 'Confermata', 'waitlist' => 'Lista attesa', 'cancelled' => 'Annullata'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="event_id">Evento</label>
                    <select id="event_id" name="event_id">
                        <option value="">Tutti gli eventi</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected($filters['event_id'] === (string) $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="actions" style="grid-column:1/-1;">
                    <button type="submit">Applica filtri</button>
                    <a href="{{ route('admin.bookings.index') }}" class="button alt">Reset</a>
                </div>
            </form>
        </div>

        <div class="panel table-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap;">
                <div>
                    <h2 class="table-title">Richieste recenti</h2>
                    <p class="table-subtitle">Dettaglio minimo ma utile per operare: cliente, profilo, evento, stato e note.</p>
                </div>
                <div class="actions">
                    <span class="badge brand">richiesta</span>
                    <span class="badge ok">confermata</span>
                    <span class="badge warning">lista attesa</span>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Evento</th>
                            <th>Posti</th>
                            <th>Stato</th>
                            <th>Note</th>
                            <th>Inserita il</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>
                                    <div style="font-weight:700;">{{ $booking->user->first_name }} {{ $booking->user->last_name }}</div>
                                    <div class="muted">{{ $booking->user->email }}</div>
                                    <div class="muted">
                                        {{ $booking->user->profile?->age_range ?? 'Eta N/D' }} / {{ $booking->user->profile?->rome_area ?? 'Zona N/D' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:700;">{{ $booking->event->title }}</div>
                                    <div class="muted">{{ $booking->event->category?->name ?? 'Categoria N/D' }}</div>
                                </td>
                                <td>{{ $booking->seats_reserved }}</td>
                                <td>
                                    <span class="badge {{
                                        match ($booking->status) {
                                            'confirmed' => 'ok',
                                            'waitlist' => 'warning',
                                            default => 'brand',
                                        }
                                    }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="muted">{{ $booking->customer_notes ?: 'Nessuna nota cliente' }}</div>
                                    @if ($booking->internal_notes)
                                        <div style="margin-top:6px;font-size:12px;color:#6a1f2b;font-weight:700;">Staff: {{ $booking->internal_notes }}</div>
                                    @endif
                                </td>
                                <td>{{ $booking->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted">Nessuna prenotazione disponibile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
@endsection
