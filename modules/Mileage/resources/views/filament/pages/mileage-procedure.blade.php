<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Procédure de remboursement des frais kilométriques
        </x-slot>
        <x-slot name="description">
            Suivez ces étapes pour obtenir le remboursement de vos déplacements professionnels.
        </x-slot>

        <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300">
            <li>
                <span class="font-medium">Encodez vos déplacements</span>
                <p class="ml-6 text-sm text-gray-500 dark:text-gray-400">Enregistrez chaque déplacement professionnel au fur et à mesure avec la distance parcourue et le motif.</p>
            </li>
            <li>
                <span class="font-medium">Générez une déclaration</span>
                <p class="ml-6 text-sm text-gray-500 dark:text-gray-400">Lorsque vous avez suffisamment de déplacements à déclarer, générez une déclaration récapitulative. Vous devez effectuer au minimum une déclaration par an.</p>
            </li>
            <li>
                <span class="font-medium">Imprimez et signez</span>
                <p class="ml-6 text-sm text-gray-500 dark:text-gray-400">Imprimez votre déclaration et apposez votre signature manuscrite.</p>
            </li>
            <li>
                <span class="font-medium">Joignez les justificatifs</span>
                <p class="ml-6 text-sm text-gray-500 dark:text-gray-400">Agrafez les éventuelles souches de frais (repas, tickets de train, parking) à votre déclaration.</p>
            </li>
            <li>
                <span class="font-medium">Déposez votre déclaration</span>
                <p class="ml-6 text-sm text-gray-500 dark:text-gray-400">Remettez le dossier complet au service Finances <strong>avant le 20 janvier</strong> de l'année suivante.</p>
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Informations utiles
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-950">
                <h4 class="font-medium text-primary-700 dark:text-primary-300">Taux de remboursement</h4>
                <p class="text-sm text-primary-600 dark:text-primary-400">Le taux kilométrique est fixé annuellement selon le barème en vigueur.</p>
            </div>
            <div class="rounded-lg bg-warning-50 p-4 dark:bg-warning-950">
                <h4 class="font-medium text-warning-700 dark:text-warning-300">Date limite</h4>
                <p class="text-sm text-warning-600 dark:text-warning-400">Toute déclaration remise après le 20 janvier ne pourra être traitée pour l'exercice concerné.</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
