<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employers\Pages\CreateEmployer;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\EditEmployer;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\ListEmployers;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\ViewEmployer;
use AcMarche\Hrm\Models\Employer;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListEmployers::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateEmployer::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Employer::factory()->create();

        Livewire::test(ViewEmployer::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Employer::factory()->create();

        Livewire::test(EditEmployer::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update an employer', function (): void {
        $record = Employer::factory()->create();
        $newData = Employer::factory()->make();

        Livewire::test(EditEmployer::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Employer::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Employer::factory()->make();

        Livewire::test(CreateEmployer::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 150 characters' => [['name' => Str::random(151)], ['name' => 'max']],
    ]);
});

describe('parent/child relationship', function (): void {
    it('returns descendants and self ids', function (): void {
        $parent = Employer::factory()->create();
        $child1 = Employer::factory()->create(['parent_id' => $parent->id]);
        $child2 = Employer::factory()->create(['parent_id' => $parent->id]);
        $unrelated = Employer::factory()->create();

        $ids = Employer::descendantsAndSelfIds($parent->id);

        expect($ids)
            ->toContain($parent->id)
            ->toContain($child1->id)
            ->toContain($child2->id)
            ->not->toContain($unrelated->id);
    });

    it('exposes parent and children relations', function (): void {
        $parent = Employer::factory()->create();
        $child = Employer::factory()->create(['parent_id' => $parent->id]);

        expect($child->parent->id)->toBe($parent->id)
            ->and($parent->children->pluck('id')->all())->toContain($child->id);
    });
});
