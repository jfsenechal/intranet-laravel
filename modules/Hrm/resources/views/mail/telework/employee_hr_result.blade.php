@component('hrm::mail.telework._layout', ['title' => 'Votre télétravail a été traité', 'logo' => $logo])
    <p>Bonjour {{ $employee->first_name }},</p>

    <p>
        Le service RH a traité votre demande de télétravail.
    </p>

    <ul>
        @if($telework->hr_validator_name)
            <li><strong>Traitée par :</strong> {{ $telework->hr_validator_name }}</li>
        @endif
        @if($telework->date_college)
            <li><strong>Date collège :</strong> {{ $telework->date_college->format('d/m/Y') }}</li>
        @endif
    </ul>

    @if($telework->hr_notes)
        <p><strong>Commentaire RH :</strong></p>
        <div style="background-color: #f1f5f9; padding: 12px; border-radius: 6px;">
            {!! $telework->hr_notes !!}
        </div>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir ma demande
        </a>
    </p>
@endcomponent
