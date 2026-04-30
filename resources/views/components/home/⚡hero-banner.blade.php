<?php

use Illuminate\Support\Carbon;
use Livewire\Component;

new class extends Component
{
    public string $today;

    public function mount(): void
    {
        $this->today = Carbon::now()->translatedFormat('l d F Y');
    }
};
?>

<div class="relative overflow-hidden rounded-3xl gradient-hero p-10 text-white shadow-2xl animate-fade-in-up">
    <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-20 -left-10 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
    <div class="relative">
        <p class="text-sm font-medium uppercase tracking-wider opacity-80">
            {{ $today }}
        </p>
        <h1 class="mt-2 text-4xl font-extrabold md:text-6xl">Bienvenue sur l'intranet</h1>
        <p class="mt-3 max-w-2xl text-lg opacity-90 md:text-xl">
            Votre portail central pour les actualités, documents et informations du personnel.
        </p>
    </div>
</div>
