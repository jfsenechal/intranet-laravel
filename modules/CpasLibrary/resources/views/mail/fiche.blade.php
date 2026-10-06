@component('cpas-library::mail._layout', ['title' => $fiche->name, 'logo' => $logo, 'message' => $message])
    <p>Bonjour,</p>

    <p>
        Une fiche de la bibliothèque vous est communiquée :
    </p>

    <ul>
        @if($fiche->category)
            <li><strong>Catégorie :</strong> {{ $fiche->category->name }}</li>
        @endif
        @if($fiche->tags->isNotEmpty())
            <li><strong>Tags :</strong> {{ $fiche->tags->pluck('name')->join(', ') }}</li>
        @endif
        @if($fiche->date_begin && $fiche->date_end)
            <li><strong>Période :</strong> du {{ $fiche->date_begin->format('d/m/Y') }} au {{ $fiche->date_end->format('d/m/Y') }}</li>
        @endif
        @if($fiche->type_document)
            <li><strong>Type de document :</strong> {{ $fiche->type_document }}</li>
        @endif
        @if($fiche->source)
            <li><strong>Source :</strong> {{ $fiche->source }}</li>
        @endif
        @if($fiche->date_rappel)
            <li><strong>Date de rappel :</strong> {{ $fiche->date_rappel->format('d/m/Y') }}</li>
        @endif
    </ul>

    @if($fiche->description)
        <div style="margin: 16px 0;">
            {!! str($fiche->description)->sanitizeHtml() !!}
        </div>
    @endif

    <p>
        <a href="{{ $url }}">Consulter la fiche</a>
    </p>
@endcomponent
