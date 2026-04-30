<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

new class extends Component
{
    public string $today;

    /** @var array{temperature: float, code: int, label: string, icon: string}|null */
    public ?array $weather = null;

    public function mount(): void
    {
        $this->today = Carbon::now()->translatedFormat('l d F Y');
        $this->weather = $this->fetchWeather();
    }

    /**
     * @return array{temperature: float, code: int, label: string, icon: string}|null
     */
    private function fetchWeather(): ?array
    {
        return Cache::remember('home.hero.weather', now()->addMinutes(15), function (): ?array {
            try {
                $response = Http::timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => 50.2287,
                    'longitude' => 5.3445,
                    'current' => 'temperature_2m,weather_code',
                    'timezone' => 'Europe/Brussels',
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $current = $response->json('current');

                if (! is_array($current)) {
                    return null;
                }

                $code = (int) ($current['weather_code'] ?? 0);

                return [
                    'temperature' => (float) ($current['temperature_2m'] ?? 0),
                    'code' => $code,
                    'label' => $this->describeWeather($code),
                    'icon' => $this->iconForWeather($code),
                ];
            } catch (Throwable) {
                return null;
            }
        });
    }

    private function describeWeather(int $code): string
    {
        return match (true) {
            $code === 0 => 'Ciel dégagé',
            in_array($code, [1, 2], true) => 'Partiellement nuageux',
            $code === 3 => 'Couvert',
            in_array($code, [45, 48], true) => 'Brouillard',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Bruine',
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => 'Pluie',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'Neige',
            in_array($code, [95, 96, 99], true) => 'Orage',
            default => 'Météo',
        };
    }

    private function iconForWeather(int $code): string
    {
        return match (true) {
            $code === 0 => '☀️',
            in_array($code, [1, 2], true) => '⛅',
            $code === 3 => '☁️',
            in_array($code, [45, 48], true) => '🌫️',
            in_array($code, [51, 53, 55, 56, 57], true) => '🌦️',
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => '🌧️',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => '❄️',
            in_array($code, [95, 96, 99], true) => '⛈️',
            default => '🌡️',
        };
    }
};
?>

<div class="relative overflow-hidden rounded-3xl gradient-hero px-8 py-5 text-white shadow-2xl animate-fade-in-up">
    <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-16 -left-10 h-56 w-56 rounded-full bg-white/5 blur-3xl"></div>
    <div class="relative flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wider opacity-80">
                {{ $today }}
            </p>
            <h1 class="mt-1 text-2xl font-extrabold md:text-3xl">Bienvenue sur l'intranet</h1>
            <p class="mt-1 max-w-2xl text-sm opacity-90 md:text-base">
                Votre portail central pour les actualités, documents et informations du personnel.
            </p>
        </div>

        @if ($weather)
            <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 backdrop-blur-md ring-1 ring-white/20">
                <span class="text-3xl leading-none animate-float">{{ $weather['icon'] }}</span>
                <div class="leading-tight">
                    <p class="text-2xl font-bold">{{ number_format($weather['temperature'], 1, ',', ' ') }}°C</p>
                    <p class="text-xs uppercase tracking-wider opacity-80">
                        Marche · {{ $weather['label'] }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
