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
        Vous pouvez nous faire part de toute modification dans les accès pour cette personne ici :
    </p>

    <p>
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir le profil
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <p style="margin-top: 24px;">
        Bien à vous,<br>
        Espace Public Numérique<br>
        Rue Victor Libert, 36<br>
        6900 Marche-en-Famenne
    </p>
@endcomponent
