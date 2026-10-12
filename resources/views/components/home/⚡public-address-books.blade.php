<?php

use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Repositories\OwnerScope;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    /**
     * Public address books are readable by every authenticated user, so the
     * owner scope (which needs a user) is bypassed and replaced by is_public.
     *
     * @return Collection<int, AddressBook>
     */
    public function addressBooks(): Collection
    {
        if (! auth()->check()) {
            return new Collection();
        }

        return AddressBook::query()
            ->withoutGlobalScope(OwnerScope::class)
            ->where('is_public', true)
            ->withCount(['contacts' => fn ($query) => $query->withoutGlobalScope(OwnerScope::class)])
            ->orderBy('name')
            ->get();
    }

    public function with(): array
    {
        return ['publicAddressBooks' => $this->addressBooks()];
    }
};
?>

<div>
    @auth
        <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.35s">
            <div class="gradient-address-book flex items-center justify-between p-4 text-white">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold">Carnets d'adresses publics</h2>
                </div>
                <span class="rounded-full bg-white/20 px-2 py-0.5 font-semibold backdrop-blur">
                    {{ $publicAddressBooks->count() }}
                </span>
            </div>
            <div class="p-4">
                @forelse ($publicAddressBooks as $index => $addressBook)
                    <a
                        href="{{ AddressBookResource::getUrl('view', ['record' => $addressBook->id], panel: 'mailing-list-panel') }}"
                        class="group flex items-center justify-between gap-3 border-b border-gray-100 py-2 last:border-0 animate-fade-in-up"
                        style="--delay: {{ 0.4 + ($index * 0.05) }}s"
                    >
                        <p class="truncate text-sm font-semibold text-gray-900 group-hover:text-teal-700">
                            {{ $addressBook->name }}
                        </p>
                        <span class="shrink-0 text-gray-500">
                            {{ $addressBook->contacts_count }} {{ str('contact')->plural($addressBook->contacts_count) }}
                        </span>
                    </a>
                @empty
                    <p class="text-center text-gray-500">Aucun carnet d'adresses public.</p>
                @endforelse
            </div>
        </div>
    @endauth
</div>
