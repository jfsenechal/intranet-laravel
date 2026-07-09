<?php

declare(strict_types=1);

use AcMarche\Security\Models\Module;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Module::query()->delete();
});

it('persists the resolved filament url on internal modules', function (): void {
    $module = Module::factory()->create([
        'id' => 22,
        'is_external' => false,
        'url' => 'organisation_agenda',
    ]);

    $this->artisan('intranet:module-set-url')->assertSuccessful();

    assertDatabaseHas(Module::class, [
        'id' => $module->id,
        'url' => 'https://agenda.marche.be',
    ]);
});

it('leaves external modules untouched', function (): void {
    $module = Module::factory()->create([
        'is_external' => true,
        'url' => 'https://salles.marche.be/',
    ]);

    $this->artisan('intranet:module-set-url')->assertSuccessful();

    assertDatabaseHas(Module::class, [
        'id' => $module->id,
        'url' => 'https://salles.marche.be/',
    ]);
});

it('clears the url of internal modules without a resolved destination', function (): void {
    $module = Module::factory()->create([
        'id' => 9999,
        'name' => 'Legacy tool',
        'is_external' => false,
        'url' => 'legacy_route',
    ]);

    $this->artisan('intranet:module-set-url')
        ->expectsOutputToContain('Modules without a URL:')
        ->assertSuccessful();

    assertDatabaseHas(Module::class, [
        'id' => $module->id,
        'url' => '',
    ]);
});

it('does not write changes on a dry run', function (): void {
    $module = Module::factory()->create([
        'id' => 22,
        'is_external' => false,
        'url' => 'organisation_agenda',
    ]);

    $this->artisan('intranet:module-set-url', ['--dry-run' => true])->assertSuccessful();

    assertDatabaseHas(Module::class, [
        'id' => $module->id,
        'url' => 'organisation_agenda',
    ]);
});
