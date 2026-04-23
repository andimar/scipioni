@php
    $title = 'Modifica Evento';
    $heading = 'Modifica evento';
    $subtitle = 'Aggiornamento rapido del contenuto, dello stato e della prenotabilita dell esperienza.';
@endphp

@extends('admin.layouts.app')

@section('content')
    <form method="post" action="{{ route('admin.events.update', $event) }}">
        @csrf
        @method('put')
        @include('admin.events._form', [
            'formTitle' => 'Scheda evento',
            'formSubtitle' => 'Qui il team puo aggiornare copy, calendario e disponibilita senza passare dal database o dalle API.',
            'submitLabel' => 'Aggiorna evento',
        ])
    </form>
@endsection
