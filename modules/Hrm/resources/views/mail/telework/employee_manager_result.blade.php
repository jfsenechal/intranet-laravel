@component('hrm::mail.telework._layout', ['title' => 'Décision de votre direction sur votre télétravail', 'logo' => $logo])
    <p>Bonjour {{ $employee->first_name }},</p>

    @if($telework->manager_validated)
        <p>
            Votre demande de télétravail a été <strong style="color: #059669;">validée</strong>
            par {{ $telework->manager_validator_name }}
            @if($telework->manager_validated_at)
                le {{ $telework->manager_validated_at->format('d/m/Y') }}
            @endif.
        </p>
        <p>Votre demande est maintenant transmise au service RH pour traitement.</p>
    @else
        <p>
            Votre demande de télétravail a été <strong style="color: #dc2626;">refusée</strong>
            par {{ $telework->manager_validator_name }}
            @if($telework->manager_validated_at)
                le {{ $telework->manager_validated_at->format('d/m/Y') }}
            @endif.
        </p>
    @endif

    @if($telework->manager_validation_notes)
        <p><strong>Commentaire du direction :</strong></p>
        <div style="background-color: #f1f5f9; padding: 12px; border-radius: 6px;">
            {!! $telework->manager_validation_notes !!}
        </div>
    @endif

    <p style="margin-top: 24px;">
        <a href="{{ $url }}"
           style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Voir ma demande
        </a>
    </p>
@endcomponent
