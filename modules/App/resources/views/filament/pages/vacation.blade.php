@php($user = auth()->user())
<x-filament-panels::page>

    <x-slot name="title">
        Vacation
    </x-slot>
    <x-filament::section>
        <x-slot name="heading">
            Message d'absence pour ma messagerie mail
        </x-slot>
        Votre message d'absence doit être activé via <a
            href="https://agenda.marche.be/SOGo/so/{{$user->username}}/Preferences#!/mailer" target="_blank">
            https://agenda.marche.be.
        </a>
        <br/><br/>
        Cliquez sur <strong>« Absence prolongée »</strong> pour configurer votre message d'absence.
        <br/><br/>
        Une fiche explicative est également disponible sur notre site : <a href="https://formation.marche.be/?p=581" target="_blank">https://formation.marche.be</a>.

    </x-filament::section>

</x-filament-panels::page>
