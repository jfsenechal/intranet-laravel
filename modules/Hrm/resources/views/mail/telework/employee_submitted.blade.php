@component('hrm::mail.telework._layout', ['title' => 'Votre demande de télétravail a été enregistrée', 'logo' => $logo, 'message' => $message ?? null])
    <p>Bonjour {{ $employee->first_name }},</p>

    <p>
        Votre demande de télétravail a bien été <strong style="color: #059669;">enregistrée</strong>.
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
            <li><strong>Vos remarques :</strong> {!! $telework->employee_notes !!}</li>
        @endif
    </ul>

    @if($director)
        <p>
            Elle a été transmise à <strong>{{ $director->first_name }} {{ $director->last_name }}</strong>
            pour validation. Vous recevrez un email dès que votre direction aura pris sa décision.
        </p>
    @else
        <p>
            Elle sera transmise à votre direction pour validation. Vous recevrez un email dès qu'une
            décision aura été prise.
        </p>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir ma demande
        </a>
    </p>
@endcomponent
