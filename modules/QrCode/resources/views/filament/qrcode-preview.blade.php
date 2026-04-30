@php
    $preview = $getPreview($getRecord());
@endphp

<div class="flex justify-center bg-white p-4 rounded-lg">
    {!! $preview !!}
</div>
