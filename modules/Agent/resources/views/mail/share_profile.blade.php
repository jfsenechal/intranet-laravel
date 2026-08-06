@component('agent::mail._layout', ['title' => 'Profil à compléter', 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        <strong>{{ $sender }}</strong> souhaiterait que vous complétiez le profil de
        <strong>{{ $profileLabel }}</strong>.
    </p>

    @if (filled($notes))
        <p style="white-space: pre-line;">{{ $notes }}</p>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Compléter le profil
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
