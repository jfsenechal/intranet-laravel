@component('hrm::mail.telework._layout', ['title' => 'Candidats supprimés', 'logo' => $logo])
    <p>Bonjour,</p>

    <p>
        Les candidats suivants n'ont plus déposé de candidature depuis plus d'un an
        et ont été supprimés automatiquement :
    </p>

    <ul>
        @foreach($candidates as $candidate)
            <li>{{ $candidate->last_name }} {{ $candidate->first_name }}</li>
        @endforeach
    </ul>
@endcomponent
