<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="progress-bar">
        <div class="progress-bar-value" style="width: {{ $getState() }}%;">
            {{ $getState() }}%
        </div>
    </div>
</x-dynamic-component>
