<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Directions\Pages\CreateDirection;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\EditDirection;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\ListDirections;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\ViewDirection;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employer;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    DirectoryEmulator::setup();
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListDirections::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateDirection::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Direction::factory()->create();

        Livewire::test(ViewDirection::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Direction::factory()->create();

        Livewire::test(EditDirection::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a direction', function (): void {
        $newData = Direction::factory()->make();

        Livewire::test(CreateDirection::class)
            ->fillForm([
                'name' => $newData->name,
                'abbreviation' => 'DIR',
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Direction::class, [
            'name' => $newData->name,
            'abbreviation' => 'DIR',
        ]);
    });

    it('can create a direction with employer', function (): void {
        $employer = Employer::factory()->create();
        $newData = Direction::factory()->make();

        Livewire::test(CreateDirection::class)
            ->fillForm([
                'name' => $newData->name,
                'employer_id' => $employer->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas(Direction::class, [
            'name' => $newData->name,
            'employer_id' => $employer->id,
        ]);
    });

    it('can update a direction', function (): void {
        $record = Direction::factory()->create();
        $newData = Direction::factory()->make();

        Livewire::test(EditDirection::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Direction::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete a direction', function (): void {
        $record = Direction::factory()->create();

        Livewire::test(ViewDirection::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        assertDatabaseMissing($record);
    });

    it('can bulk delete directions', function (): void {
        $records = Direction::factory(3)->create();

        Livewire::test(ListDirections::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $records->each(fn (Direction $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Direction::factory()->make();

        Livewire::test(CreateDirection::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 100 characters' => [['name' => Str::random(101)], ['name' => 'max']],
        '`abbreviation` is max 255 characters' => [['abbreviation' => Str::random(256)], ['abbreviation' => 'max']],
    ]);
});

describe('relationships', function (): void {
    it('belongs to an employer', function (): void {
        $employer = Employer::factory()->create();
        $direction = Direction::factory()->create(['employer_id' => $employer->id]);

        expect($direction->employer->id)->toBe($employer->id);
    });
});
