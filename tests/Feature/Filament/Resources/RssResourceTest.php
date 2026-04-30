<?php

declare(strict_types=1);

use AcMarche\App\Enums\RssFeedEnum;
use AcMarche\App\Filament\Resources\Rsses\Pages\CreateRss;
use AcMarche\App\Filament\Resources\Rsses\Pages\EditRss;
use AcMarche\App\Filament\Resources\Rsses\Pages\ListRsses;
use AcMarche\App\Models\Rss;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Http::fake([
        '*' => Http::response(
            '<?xml version="1.0"?><rss><channel><item><title>x</title></item></channel></rss>',
            200,
        ),
    ]);
});

it('exposes predefined feeds from the enum', function () {
    expect(RssFeedEnum::options())
        ->toHaveCount(4)
        ->toHaveKey('https://www.uvcw.be/rss/fil-rss.xml');
});

it('can render the index page', function () {
    livewire(ListRsses::class)->assertOk();
});

it('only shows current user\'s feeds', function () {
    $otherUser = User::factory()->create();

    $mine = Rss::query()->create([
        'user_id' => auth()->id(),
        'name' => 'Mine',
        'url' => 'https://example.com/mine.xml',
    ]);

    $theirs = Rss::query()->create([
        'user_id' => $otherUser->id,
        'name' => 'Theirs',
        'url' => 'https://example.com/theirs.xml',
    ]);

    livewire(ListRsses::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$theirs]);
});

it('assigns the authenticated user when creating', function () {
    livewire(CreateRss::class)
        ->fillForm([
            'name' => 'Custom feed',
            'url' => 'https://example.com/feed.xml',
        ])
        ->call('create');

    assertDatabaseHas(Rss::class, [
        'user_id' => auth()->id(),
        'name' => 'Custom feed',
        'url' => 'https://example.com/feed.xml',
    ]);
});

it('requires name and url', function () {
    livewire(CreateRss::class)
        ->fillForm([
            'name' => null,
            'url' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'url' => 'required',
        ]);
});

it('can render the edit page for an owned feed', function () {
    $rss = Rss::query()->create([
        'user_id' => auth()->id(),
        'name' => 'Feed',
        'url' => 'https://example.com/feed.xml',
    ]);

    livewire(EditRss::class, ['record' => $rss->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => 'Feed',
            'url' => 'https://example.com/feed.xml',
        ]);
});
