@component('agent::mail._layout', ['title' => 'Nouveau compte informatique', 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        Suivant votre demande, nous avons créé un nouveau compte pour
        <strong>{{ $profileLabel }}</strong>.<br>
        Merci de transmettre ces informations à la personne concernée (imprimez la pièce jointe).
    </p>

    @if (! empty($notes))
        <p><strong>Remarques :</strong></p>
        <div style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 6px; white-space: pre-wrap;">{{ $notes }}</div>
    @endif

    <p style="margin-top: 24px;">
        Bien à vous,<br>
        Espace Public Numérique<br>
        Rue Victor Libert, 36<br>
        6900 Marche-en-Famenne
    </p>
@endcomponent
