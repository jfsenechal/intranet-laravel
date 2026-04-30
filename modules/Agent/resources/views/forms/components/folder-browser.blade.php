@php
    $statePath = $getStatePath();
    $tree = $field->getTree();
    $breadcrumbs = $field->getBreadcrumbs();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            tree: @js($tree),
            breadcrumbs: @js($breadcrumbs),
            selected: $wire.$entangle(@js($statePath)),
            path: [],

            childrenOf(id) {
                return this.tree[id ?? 0] ?? [];
            },

            isLeaf(id) {
                return this.childrenOf(id).length === 0;
            },

            columns() {
                const cols = [this.childrenOf(0)];
                for (const id of this.path) {
                    const children = this.childrenOf(id);
                    if (children.length === 0) break;
                    cols.push(children);
                }
                return cols;
            },

            currentId() {
                return this.path.length > 0 ? this.path[this.path.length - 1] : null;
            },

            canAdd() {
                const id = this.currentId();
                if (id === null) return false;
                return !(this.selected ?? []).map(Number).includes(Number(id));
            },

            selectAt(columnIndex, id) {
                this.path = this.path.slice(0, columnIndex);
                this.path.push(id);
            },

            goTo(index) {
                this.path = this.path.slice(0, index);
            },

            addCurrent() {
                if (!this.canAdd()) return;
                const id = Number(this.currentId());
                const current = [...(this.selected ?? [])].map(Number);
                if (!current.includes(id)) current.push(id);
                this.selected = current;
                this.path = [];
            },

            remove(id) {
                this.selected = (this.selected ?? []).map(Number).filter(v => v !== Number(id));
            },

            labelFor(id) {
                return this.breadcrumbs[id] ?? '';
            },

            breadcrumbLabel(id) {
                const list = this.childrenOf(this.path.indexOf(id) > 0 ? this.path[this.path.indexOf(id) - 1] : 0);
                const found = list.find(f => Number(f.id) === Number(id));
                return found ? found.name : '';
            },
        }"
        class="fi-folder-browser space-y-3"
    >
        <div class="rounded-lg border border-gray-200 bg-sky-50 px-4 py-2 text-sm text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
            Sélectionnez le ou les dossiers sur le serveur data que l'agent pourra accèder
        </div>

        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/5">
            <nav class="flex flex-1 flex-wrap items-center gap-1 text-sm">
                <button
                    type="button"
                    class="text-primary-600 hover:underline dark:text-primary-400"
                    @click="goTo(0)"
                >Racine</button>
                <template x-for="(id, index) in path" :key="index + '-' + id">
                    <span class="flex items-center gap-1">
                        <span class="text-gray-400">/</span>
                        <template x-if="index < path.length - 1">
                            <button
                                type="button"
                                class="text-primary-600 hover:underline dark:text-primary-400"
                                x-text="breadcrumbLabel(id)"
                                @click="goTo(index + 1)"
                            ></button>
                        </template>
                        <template x-if="index === path.length - 1">
                            <span class="text-gray-700 dark:text-gray-200" x-text="breadcrumbLabel(id)"></span>
                        </template>
                    </span>
                </template>
            </nav>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md bg-danger-600 p-2 text-white hover:bg-danger-500 disabled:opacity-50"
                :disabled="path.length === 0"
                @click="path = []"
                title="Réinitialiser la navigation"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
        </div>

        <div class="flex gap-2 overflow-x-auto">
            <template x-for="(column, columnIndex) in columns()" :key="columnIndex">
                <div class="min-w-[12rem] flex-1 rounded-md border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
                    <ul class="max-h-60 overflow-y-auto py-1 text-sm">
                        <template x-for="folder in column" :key="folder.id">
                            <li>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between px-3 py-1 text-left hover:bg-gray-100 dark:hover:bg-white/10"
                                    :class="Number(path[columnIndex]) === Number(folder.id) ? 'bg-gray-200 dark:bg-white/10 font-medium' : ''"
                                    @click="selectAt(columnIndex, folder.id)"
                                >
                                    <span x-text="folder.name" class="truncate"></span>
                                    <span
                                        x-show="!isLeaf(folder.id)"
                                        class="ml-2 text-gray-400"
                                        aria-hidden="true"
                                    >›</span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>

        <div>
            <button
                type="button"
                class="fi-btn inline-flex items-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900"
                :disabled="!canAdd()"
                @click="addCurrent()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Ajouter ce dossier</span>
            </button>
        </div>

        <div class="space-y-1" x-show="(selected ?? []).length > 0">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Dossiers sélectionnés (<span x-text="(selected ?? []).length"></span>)
            </div>
            <div class="max-h-72 overflow-y-auto rounded-md border border-gray-200 dark:border-white/10">
                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    <template x-for="id in (selected ?? [])" :key="id">
                        <li class="flex items-center justify-between px-3 py-2 text-sm">
                            <span x-text="labelFor(id)" class="text-gray-800 dark:text-gray-100"></span>
                            <button
                                type="button"
                                class="rounded-md p-1 text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                                @click="remove(id)"
                                title="Retirer"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</x-dynamic-component>
