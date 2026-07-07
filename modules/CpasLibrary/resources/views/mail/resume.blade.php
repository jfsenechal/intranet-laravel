@component('cpas-library::mail._layout', ['title' => 'Résumé de la semaine', 'logo' => $logo])
    <p>Bonjour,</p>

    <p>
        Voici les fiches ajoutées à la bibliothèque au cours des sept derniers jours :
    </p>

    <ul>
        @foreach($fiches as $fiche)
            <li style="margin-bottom: 8px;">
                @isset($urls[$fiche->id])
                    <a href="{{ $urls[$fiche->id] }}">{{ $fiche->name }}</a>
                @else
                    <strong>{{ $fiche->name }}</strong>
                @endisset
                @if($fiche->createdAt)
                    <span style="color: #64748b;"> &mdash; {{ $fiche->createdAt->format('d/m/Y') }}</span>
                @endif
            </li>
        @endforeach
    </ul>
@endcomponent
