<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\CreateTag;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\EditTag;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\ListTags;
use AcMarche\CpasLibrary\Filament\Resources\Tags\Pages\ViewTag;
use AcMarche\CpasLibrary\Models\Tag;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('cpas-library-panel'));

    $this->admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY_ADMIN->value]);
    $this->admin->roles()->attach($adminRole);

    $this->member = User::factory()->create();
    $memberRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]);
    $this->member->roles()->attach($memberRole);

    $this->actingAs($this->admin);
});

it('renders the list, create, view and edit pages', function (): void {
    $tag = Tag::factory()->create();

    livewire(ListTags::class)->assertOk();
    livewire(CreateTag::class)->assertOk();
    livewire(ViewTag::class, ['record' => $tag->id])->assertOk();
    livewire(EditTag::class, ['record' => $tag->id])->assertOk();
});

it('lists tags', function (): void {
    $tags = Tag::factory(3)->create();

    livewire(ListTags::class)
        ->loadTable()
        ->assertCanSeeTableRecords($tags);
});

it('creates a tag', function (): void {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Urgent',
            'slug' => 'urgent',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Tag::class, ['name' => 'Urgent', 'slug' => 'urgent']);
});

it('updates a tag', function (): void {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->id])
        ->fillForm(['name' => 'Renamed'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tag::class, ['id' => $tag->id, 'name' => 'Renamed']);
});

it('deletes a tag from view page', function (): void {
    $tag = Tag::factory()->create();

    livewire(ViewTag::class, ['record' => $tag->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertDatabaseMissing($tag);
});

it('validates the form data', function (array $data, array $errors): void {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->id])
        ->fillForm([
            'name' => 'Valid name',
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
]);

it('rejects a duplicate tag name', function (): void {
    Tag::factory()->create(['name' => 'duplicate']);

    livewire(CreateTag::class)
        ->fillForm(['name' => 'duplicate'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertNotNotified();
});

it('forbids a user without library role from listing tags', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListTags::class)->assertForbidden();
});

it('forbids a ROLE_LIBRARY user from creating a tag', function (): void {
    $this->actingAs($this->member);

    livewire(CreateTag::class)->assertForbidden();
});

it('hides the create header action for a ROLE_LIBRARY user', function (): void {
    $this->actingAs($this->member);

    livewire(ListTags::class)->assertActionHidden(CreateAction::class);
});
