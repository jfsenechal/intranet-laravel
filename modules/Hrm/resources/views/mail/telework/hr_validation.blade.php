@component('hrm::mail.telework._layout', ['title' => 'Télétravail à traiter', 'logo' => $logo])
    <p>Bonjour,</p>

    <p>
        Une demande de télétravail validée par la direction attend votre traitement.
    </p>

    @if($employee)
        <ul>
            <li><strong>Agent :</strong> {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->username }})</li>
            <li><strong>Validée par :</strong> {{ $telework->manager_validator_name }}
                @if($telework->manager_validated_at)
                    le {{ $telework->manager_validated_at->format('d/m/Y') }}
                @endif
            </li>
        </ul>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Consulter et valider la demande
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
