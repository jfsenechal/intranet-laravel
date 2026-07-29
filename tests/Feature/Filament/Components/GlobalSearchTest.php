<?php

declare(strict_types=1);

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Livewire\GlobalSearch;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    /* UserResource lives in the admin panel; its global search results build URLs
       from the current panel, which otherwise defaults to one without that resource. */
    Filament::setCurrentPanel(Filament::getPanel('admin-panel'));
});

it('can global search', function (): void {
    livewire(GlobalSearch::class)
        ->set('search', 'test')
        ->assertOk();
});

it('can global search for users', function (string $attribute): void {
    $record = User::factory()->create();

    UserResource::getGlobalSearchResults($record->{$attribute})
        ->each(function (GlobalSearchResult $result) use ($record): void {
            expect($result->title)->toBe($record->name);
        });
})->with([
    'name',
    'email',
]);
