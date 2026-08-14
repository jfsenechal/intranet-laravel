<div class="w-full rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
    @if (str_starts_with($contentType, 'image/'))
        <img
            src="{{ $url }}"
            alt="{{ $filename }}"
            class="mx-auto max-h-[600px] rounded-lg object-contain lg:max-h-[calc(100vh-10rem)]"
        />
    @elseif ($contentType === 'application/pdf')
        <iframe
            src="{{ $url }}"
            class="h-[600px] w-full rounded-lg border-0 lg:h-[calc(100vh-10rem)]"
            title="{{ $filename }}"
        ></iframe>

        {{-- Navigateurs configurés pour télécharger les PDF au lieu de les ouvrir : le cadre reste vide. --}}
        <details class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <summary class="cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200">
                Le document ne s&rsquo;affiche pas ?
            </summary>

            <div class="mt-2 space-y-3">
                <p>
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        rel="noopener"
                        class="font-medium text-primary-600 underline dark:text-primary-400"
                    >Ouvrir dans un nouvel onglet</a>
                    &mdash; solution immédiate, sans rien modifier.
                </p>

                <p>
                    Si le cadre reste vide, votre navigateur est réglé pour télécharger les PDF
                    plutôt que pour les afficher. Pour changer ce réglage :
                </p>

                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Chrome / Edge</p>
                    <ol class="ml-4 list-decimal space-y-0.5">
                        <li>
                            Copiez <code class="rounded bg-gray-200 px-1 dark:bg-gray-700">chrome://settings/content/pdfDocuments</code>
                            dans la barre d&rsquo;adresse (sur Edge :
                            <code class="rounded bg-gray-200 px-1 dark:bg-gray-700">edge://settings/content/pdfDocuments</code>),
                            puis appuyez sur Entrée.
                        </li>
                        <li>Choisissez <strong>Ouvrir les PDF dans Chrome</strong> (et non « Télécharger les PDF »).</li>
                        <li>Revenez sur cette page et actualisez-la (F5).</li>
                    </ol>
                </div>

                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Firefox</p>
                    <ol class="ml-4 list-decimal space-y-0.5">
                        <li>Menu ☰ &rsaquo; <strong>Paramètres</strong> &rsaquo; onglet <strong>Général</strong>.</li>
                        <li>Descendez jusqu&rsquo;à la section <strong>Applications</strong>.</li>
                        <li>
                            Cherchez <strong>Portable Document Format (PDF)</strong> et sélectionnez
                            <strong>Ouvrir dans Firefox</strong> dans la liste de droite
                            (et non « Enregistrer le fichier »).
                        </li>
                        <li>Revenez sur cette page et actualisez-la (F5).</li>
                    </ol>
                </div>

                <p>
                    Si le problème persiste après ces réglages, prévenez le service informatique :
                    il s&rsquo;agit alors d&rsquo;un blocage côté serveur et non d&rsquo;un réglage de votre poste.
                </p>
            </div>
        </details>
    @else
        <div class="flex flex-col items-center justify-center py-8">
            <x-filament::icon
                icon="tabler-file"
                class="mb-2 h-12 w-12 text-gray-400"
            />
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $filename }}
            </p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                {{ $contentType }}
            </p>
        </div>
    @endif
</div>
