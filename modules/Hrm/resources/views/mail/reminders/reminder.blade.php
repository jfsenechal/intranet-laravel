@component('hrm::mail.telework._layout', ['title' => 'Rappel : '.$reminderType, 'logo' => $logo])
    <p>Bonjour,</p>

    <p>
        Un rappel <strong>{{ $reminderType }}</strong> est programmé pour aujourd'hui.
    </p>

    @if($employeeName)
        <ul>
            <li><strong>Agent :</strong> {{ $employeeName }}</li>
        </ul>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Consulter la fiche
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
