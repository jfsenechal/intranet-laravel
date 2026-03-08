<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="fi-sc-flex">
        @if(!empty($getState()))
            <iframe
                class="fi-growable"
                src="{{ $getRoute($getState()) }}?r=<?php echo mt_rand() ?>" style="min-height: {{ $getMinHeight() }};">
            </iframe>
        @elseif(!empty($getFileUrl()))
            <iframe
                class="fi-growable"
                src="{{ $getFileUrl() }}?r=<?php echo mt_rand() ?>" style="min-height: {{ $getMinHeight() }};">
            </iframe>
        @endif
    </div>
</x-dynamic-component>
