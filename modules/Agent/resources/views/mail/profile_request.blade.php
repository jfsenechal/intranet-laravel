@component('agent::mail._layout', ['title' => 'Demande de compte informatique', 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        Le Grh a fait une demande de compte informatique pour
        <strong>{{ $employeeLabel }}</strong>.
    </p>

    <p>
        Merci de créer son profil et de l'envoyer au responsable pour le compléter.
    </p>

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir et valider la création du profil
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
