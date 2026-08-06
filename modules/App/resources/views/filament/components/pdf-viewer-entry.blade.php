@php
    $state = $getState();
    // An empty src makes the browser re-request the current page, which renders
    // the whole page inside the iframe, so only draw it once a url is resolved.
    $url = filled($state) ? $getRoute($state) : $getFileUrl();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="fi-sc-flex">
        @if (filled($url))
            <iframe
                class="fi-growable"
                src="{{ $url }}?r=<?php echo mt_rand() ?>" style="min-height: {{ $getMinHeight() }};">
            </iframe>
        @endif
    </div>
</x-dynamic-component>
