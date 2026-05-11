@php($user = auth()->user())
<x-filament-panels::page>

    <x-slot name="title">
        Vacation
    </x-slot>
    <x-filament::section>
        <x-slot name="heading">
            Message d'absence pour ma messagerie mail
        </x-slot>
        Votre message d'absence peut être activé via :<br/>
        <a href="https://agenda.marche.be/SOGo/so/{{$user->username}}/Preferences#!/mailer" target="_blank">https://agenda.marche.be</a>
        <br/><br/> Cliquez sur "Absence prolongée"
 <br/><br/>
        <a href="https://formation.marche.be/?p=581" target="_blank">
            Vous pouvez trouver de l'aide sur notre site Formation.marche.be
        </a>

    </x-filament::section>

</x-filament-panels::page>
