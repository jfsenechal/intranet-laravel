<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Service;

it('truncates the generated direction slug to fit the 80-character slug column', function (): void {
    $longName = str_repeat('Direction Generale des Ressources ', 3);

    $direction = Direction::factory()->create([
        'name' => $longName,
        'slug' => null,
    ]);

    expect(mb_strlen($direction->slug))->toBeLessThanOrEqual(80)
        ->and($direction->slug)->toStartWith('direction-generale');
});

it('truncates the generated service slug to fit the 80-character slug column', function (): void {
    $longName = str_repeat('Service Population Etat Civil ', 4);

    $service = Service::factory()->create([
        'name' => $longName,
        'slug' => null,
    ]);

    expect(mb_strlen($service->slug))->toBeLessThanOrEqual(80)
        ->and($service->slug)->toStartWith('service-population');
});

it('keeps truncated reference slugs unique for identical long names', function (): void {
    $longName = str_repeat('Direction Commune Partagee ', 4);

    $first = Direction::factory()->create(['name' => $longName, 'slug' => null]);
    $second = Direction::factory()->create(['name' => $longName, 'slug' => null]);

    expect(mb_strlen($second->slug))->toBeLessThanOrEqual(80)
        ->and($second->slug)->not->toBe($first->slug);
});
