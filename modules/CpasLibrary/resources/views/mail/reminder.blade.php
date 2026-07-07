@component('cpas-library::mail._layout', ['title' => 'Rappels du jour', 'logo' => $logo])
    <p>Bonjour,</p>

    <p>
        Les fiches suivantes ont un rappel programmé pour aujourd'hui :
    </p>

    <ul>
        @foreach($fiches as $fiche)
            <li style="margin-bottom: 8px;">
                @isset($urls[$fiche->id])
                    <a href="{{ $urls[$fiche->id] }}">{{ $fiche->name }}</a>
                @else
                    <strong>{{ $fiche->name }}</strong>
                @endisset
                @if($fiche->date_rappel)
                    <span style="color: #64748b;"> &mdash; {{ $fiche->date_rappel->format('d/m/Y') }}</span>
                @endif
            </li>
        @endforeach
    </ul>
@endcomponent
