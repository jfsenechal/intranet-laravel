@extends('mailing-list::unsubscribe.layout')

@section('title', 'Se désabonner')

@section('content')
    <h1>Se désabonner</h1>
    <p>
        Vous êtes sur le point de vous désabonner de nos envois avec l'adresse
        <strong>{{ $recipient->email_address }}</strong>.
    </p>
    <p>Vous ne recevrez plus de messages de notre part. Confirmez-vous ?</p>

    <form method="POST" action="{{ $confirmUrl }}">
        @csrf
        <button type="submit" class="btn">Confirmer le désabonnement</button>
    </form>
@endsection
