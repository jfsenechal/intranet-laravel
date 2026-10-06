<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Pages\LibraryIndex;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
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

it('links to the fiche create page for each fiche type', function (FicheTypeEnum $type): void {
    livewire(LibraryIndex::class)
        ->assertActionVisible('create_'.$type->value)
        ->assertActionHasUrl('create_'.$type->value, FicheResource::getUrl('create', ['type' => $type->value]));
})->with(FicheTypeEnum::cases());

it('lists the ten latest fiches without absences', function (): void {
    $oldest = Fiche::factory()->create(['type' => FicheTypeEnum::DEFAULT->value, 'createdAt' => now()->subDays(20)]);
    $recent = Fiche::factory(10)->create(['type' => FicheTypeEnum::LEGISLATION->value, 'createdAt' => now()->subDay()]);
    $absence = Fiche::factory()->create(['type' => FicheTypeEnum::ABSENCE->value, 'createdAt' => now()]);

    $latest = livewire(LibraryIndex::class)->instance()->getLatestFiches();

    expect($latest->pluck('id')->all())
        ->toHaveCount(10)
        ->toEqualCanonicalizing($recent->pluck('id')->all())
        ->not->toContain($oldest->id)
        ->not->toContain($absence->id);
});

it('lists current and upcoming absences, soonest first', function (): void {
    $later = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'name' => 'Absence plus tard',
        'date_begin' => now()->addDays(10),
        'date_end' => now()->addDays(12),
    ]);
    $current = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'name' => 'Absence en cours',
        'date_begin' => now()->subDay(),
        'date_end' => now()->addDay(),
    ]);
    $ended = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'date_begin' => now()->subDays(5),
        'date_end' => now()->subDays(2),
    ]);
    Fiche::factory()->create(['type' => FicheTypeEnum::DEFAULT->value]);

    expect(livewire(LibraryIndex::class)->instance()->getAbsences()->pluck('id')->all())
        ->toBe([$current->id, $later->id]);

    livewire(LibraryIndex::class)
        ->assertSee('Absence en cours')
        ->assertSee('Absence plus tard');
});
