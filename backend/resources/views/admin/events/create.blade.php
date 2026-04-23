@php
    $title = 'Nuovo Evento';
    $heading = 'Nuovo evento';
    $subtitle = 'Creazione rapida di un contenuto eventi con i campi indispensabili per staff e app.';
@endphp

@extends('admin.layouts.app')

@section('content')
    <form method="post" action="{{ route('admin.events.store') }}">
        @csrf
        @include('admin.events._form', [
            'formTitle' => 'Crea un nuovo evento',
            'formSubtitle' => 'Versione operativa essenziale: titolo, scheduling, prezzo, stato e copy pronto per l area riservata.',
            'submitLabel' => 'Salva evento',
        ])
    </form>
@endsection
