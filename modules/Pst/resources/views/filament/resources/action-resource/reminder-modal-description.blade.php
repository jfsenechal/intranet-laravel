<p class="fi-modal-description text-sm text-gray-500 dark:text-gray-400 mt-2">
    Vous trouvez que le projet n'avance pas. Houspiller les agents!</p>
<h3 class="text-lg my-2">Destinataires</h3>
<div class="flex flex-row gap-2 items-start">
    @foreach($emails as $recipient)
        <x-filament::badge icon="tabler-mail" size="sm">
            {{ $recipient }}
        </x-filament::badge>
    @endforeach
</div>
