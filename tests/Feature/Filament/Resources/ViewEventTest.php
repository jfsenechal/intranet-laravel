<?php

declare(strict_types=1);

use AcMarche\AldermenAgenda\Filament\Resources\Event\Pages\ViewEvent;
use AcMarche\AldermenAgenda\Models\Event;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

function createAldermenEvent(array $attributes = []): Event
{
    return Event::create([
        'name' => 'Officialisation du jumelage',
        'event_type' => 'Invitation',
        'organizer' => 'Organisé par la Ville',
        'description' => 'Programme de la journée',
        'start_at' => '2026-09-05 08:30:00',
        'end_at' => '2026-09-05 14:30:00',
        'location' => 'Hôtel de Ville de Marche',
        ...$attributes,
    ]);
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('aldermen-agenda-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('can render the view page', function (): void {
    $event = createAldermenEvent();

    livewire(ViewEvent::class, ['record' => $event->id])
        ->assertOk();
});

it('renders the description line breaks as html', function (): void {
    $event = createAldermenEvent(['description' => "Première ligne\nDeuxième ligne"]);

    livewire(ViewEvent::class, ['record' => $event->id])
        ->assertOk()
        ->assertSeeHtml('Première ligne<br');
});

it('escapes html in the description', function (): void {
    $event = createAldermenEvent(['description' => '<script>alert(1)</script>']);

    livewire(ViewEvent::class, ['record' => $event->id])
        ->assertOk()
        ->assertDontSeeHtml('<script>alert(1)</script>');
});
