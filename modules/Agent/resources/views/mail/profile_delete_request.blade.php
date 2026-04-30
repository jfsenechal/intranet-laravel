@component('agent::mail._layout', ['title' => 'Suppression d\'un compte informatique', 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        Le Grh demande la suppression du compte informatique de
        <strong>{{ $employeeLabel }}</strong>@if (! empty($username)) (<code>{{ $username }}</code>)@endif.
    </p>

    <p>
        Merci de procéder à la suppression du profil et des accès associés.
    </p>
@endcomponent
