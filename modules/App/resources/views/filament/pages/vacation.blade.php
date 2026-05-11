@php($user = auth()->user())
<x-filament-panels::page>

    <x-slot name="title">
        Vacation
    </x-slot>

    <x-filament::callout
        icon="heroicon-o-information-circle"
        color="info"
    >
        <x-slot name="heading">
            Votre message d'absence
        </x-slot>

        <x-slot name="description">
            Votre message d'absence peut être activé via :<br/>
            <a href="https://agenda.marche.be/SOGo/so/{{$user.username}}/Preferences#!/mailer">agenda.marche.be</a>
           <br/><br/> Cliquez sur "Absence prolongée"
        </x-slot>
    </x-filament::callout>

</x-filament-panels::page>
