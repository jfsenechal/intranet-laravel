@component('agent::mail._layout', ['title' => 'Changements sur un profil', 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        <strong>{{ $sender }}</strong> signale des changements sur le profil de
        <strong>{{ $profileLabel }}</strong>.
    </p>

    <p><strong>Changements :</strong></p>
    <div style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 6px; white-space: pre-wrap;">{{ $notes }}</div>

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir le profil
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
