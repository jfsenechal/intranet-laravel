<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Pages\LibraryIndex;
use AcMarche\CpasLibrary\Models\Category;
use AcMarche\CpasLibrary\Models\Fiche;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('cpas-library-panel'));

    $this->member = User::factory()->create();
    $memberRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]);
    $this->member->roles()->attach($memberRole);

    $this->actingAs($this->member);
});

it('renders the library index page', function (): void {
    livewire(LibraryIndex::class)->assertOk();
});

it('shows parent categories with their direct children', function (): void {
    $parent = Category::factory()->create(['name' => 'Aide sociale', 'parent_id' => null]);
    $child = Category::factory()->create(['name' => 'Législation', 'parent_id' => $parent->id]);
    Fiche::factory(3)->create(['category_id' => $child->id]);

    livewire(LibraryIndex::class)
        ->assertSee('Aide sociale')
        ->assertSee('Législation')
        ->assertSee('3 fiches');
});

it('forbids a user without a library role', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(LibraryIndex::class)->assertForbidden();
});

it('lists only parent categories at the top level', function (): void {
    $parent = Category::factory()->create(['name' => 'Racine', 'parent_id' => null]);
    Category::factory()->create(['name' => 'Enfant', 'parent_id' => $parent->id]);

    $categories = livewire(LibraryIndex::class)->instance()->getParentCategories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first()->name)->toBe('Racine')
        ->and($categories->first()->children)->toHaveCount(1);
});
