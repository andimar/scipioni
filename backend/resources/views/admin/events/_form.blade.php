<div class="stack">
    <div class="panel hero-panel">
        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0 0 8px;font-family:Georgia, 'Times New Roman', serif;font-size:28px;">{{ $formTitle }}</h2>
                <p class="table-subtitle">{{ $formSubtitle }}</p>
            </div>
            @if ($event->exists)
                <div class="user-chip">
                    <span>Slug</span>
                    <strong>{{ $event->slug }}</strong>
                </div>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="form-grid">
            <div class="field full">
                <label for="title">Titolo</label>
                <input id="title" name="title" type="text" value="{{ old('title', $event->title) }}" required>
            </div>

            <div class="field full">
                <label for="subtitle">Sottotitolo</label>
                <input id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $event->subtitle) }}">
            </div>

            <div class="field">
                <label for="category_id">Categoria</label>
                <select id="category_id" name="category_id">
                    <option value="">Seleziona categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $event->category_id) === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="venue_name">Location</label>
                <input id="venue_name" name="venue_name" type="text" value="{{ old('venue_name', $event->venue_name) }}" required>
            </div>

            <div class="field">
                <label for="venue_address">Indirizzo</label>
                <input id="venue_address" name="venue_address" type="text" value="{{ old('venue_address', $event->venue_address) }}">
            </div>

            <div class="field">
                <label for="starts_at">Inizio evento</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($event->starts_at)->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div class="field">
                <label for="ends_at">Fine evento</label>
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($event->ends_at)->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="field">
                <label for="capacity">Capienza</label>
                <input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', $event->capacity) }}" required>
            </div>

            <div class="field">
                <label for="price">Prezzo</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $event->price) }}" required>
            </div>

            <div class="field">
                <label for="status">Stato contenuto</label>
                <select id="status" name="status" required>
                    @foreach (['draft' => 'Bozza', 'published' => 'Pubblicato', 'archived' => 'Archiviato'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="booking_status">Stato prenotazioni</label>
                <select id="booking_status" name="booking_status" required>
                    @foreach (['open' => 'Aperte', 'closed' => 'Chiuse', 'waitlist' => 'Lista attesa'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('booking_status', $event->booking_status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field full">
                <label for="short_description">Abstract breve</label>
                <textarea id="short_description" name="short_description">{{ old('short_description', $event->short_description) }}</textarea>
            </div>

            <div class="field full">
                <label for="full_description">Descrizione completa</label>
                <textarea id="full_description" name="full_description" required>{{ old('full_description', $event->full_description) }}</textarea>
            </div>

            <div class="field full">
                <label>Opzioni</label>
                <div class="checkbox-row">
                    <label class="checkbox-card">
                        <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $event->requires_approval))>
                        Richiede approvazione staff
                    </label>
                    <label class="checkbox-card">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $event->is_featured))>
                        Metti in evidenza in home
                    </label>
                </div>
            </div>
        </div>

        <div class="actions" style="margin-top:22px;">
            <button type="submit">{{ $submitLabel }}</button>
            <a href="{{ route('admin.events.index') }}" class="button alt">Torna alla lista</a>
        </div>
    </div>
</div>
