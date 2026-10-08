@extends('mailing-list::unsubscribe.layout')

@section('title', 'Désabonnement confirmé')

@section('content')
    <h1>Désabonnement confirmé</h1>
    <p>
        L'adresse <strong>{{ $recipient->email_address }}</strong> a bien été désabonnée.
    </p>
    <p>Vous ne recevrez plus de messages de notre part.</p>
@endsection
