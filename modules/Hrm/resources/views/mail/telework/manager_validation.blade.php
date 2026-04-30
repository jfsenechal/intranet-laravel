@component('hrm::mail.telework._layout', ['title' => 'Nouvelle demande de télétravail', 'logo' => $logo])
    <p>Bonjour {{ $director->first_name }},</p>

    <p>
        <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
        ({{ $employee->username }}) a introduit une demande de télétravail et attend votre validation.
    </p>

    <ul>
        <li><strong>Lieu :</strong> {{ $telework->location_type?->getLabel() }}</li>
        <li><strong>Type de jour :</strong> {{ $telework->day_type?->getLabel() }}</li>
        @if($telework->fixed_day)
            <li><strong>Jour fixe :</strong> {{ $telework->fixed_day?->getLabel() }}</li>
        @endif
        @if($telework->variable_day_reason)
            <li><strong>Motivation :</strong> {!! $telework->variable_day_reason !!}</li>
        @endif
        @if($telework->employee_notes)
            <li><strong>Remarques de l'agent :</strong> {!! $telework->employee_notes !!}</li>
        @endif
    </ul>

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #059669; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Valider ou refuser la demande
        </a>
    </p>

    <p style="font-size: 13px; color: #64748b;">
        Ou copiez ce lien : <a href="{{ $url }}">{{ $url }}</a>
    </p>
@endcomponent
