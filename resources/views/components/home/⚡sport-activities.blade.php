<?php

use Livewire\Component;

new class extends Component
{
};
?>

<div class="card-hover relative overflow-hidden rounded-2xl shadow-lg animate-fade-in-up" style="--delay: 0.2s">
    <div class="gradient-sport absolute inset-0"></div>
    <div class="relative flex flex-col gap-3 p-5 text-white">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur animate-float">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h2 class="text-lg font-extrabold">Activités sportives</h2>
        </div>
        <p class="text-sm opacity-90">Pour le personnel</p>
        <div class="flex items-center gap-3 text-3xl">
            <span class="animate-float">⚽</span>
            <span class="animate-float [animation-delay:300ms]">🏃</span>
            <span class="animate-float [animation-delay:600ms]">🏋️</span>
            <span class="animate-float [animation-delay:900ms]">🚴</span>
        </div>
    </div>
</div>
