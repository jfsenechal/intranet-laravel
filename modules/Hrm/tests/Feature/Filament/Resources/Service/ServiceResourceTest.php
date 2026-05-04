<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Services\Pages\CreateService;
use AcMarche\Hrm\Filament\Resources\Services\Pages\EditService;
use AcMarche\Hrm\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Hrm\Filament\Resources\Services\Pages\ViewService;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\Service;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListServices::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateService::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('table columns', function (): void {
    it('has column', function (string $column): void {
        Livewire::test(ListServices::class)
            ->assertTableColumnExists($column);
    })->with(['name', 'abbreviation', 'direction.name', 'phone', 'email']);

    it('can sort by name', function (): void {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function (): void {
        $records = Service::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function (): void {
    it('can create a service', function (): void {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                'abbreviation' => 'TST',
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Service::class, [
            'name' => $newData->name,
            'abbreviation' => 'TST',
        ]);
    });

    it('can create a service with relationships', function (): void {
        $employer = Employer::factory()->create();
        $direction = Direction::factory()->create();
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                'employer_id' => $employer->id,
                'direction_id' => $direction->id,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Service::class, [
            'name' => $newData->name,
            'employer_id' => $employer->id,
            'direction_id' => $direction->id,
        ]);
    });

    it('can update a service', function (): void {
        $record = Service::factory()->create();
        $newData = Service::factory()->make();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Service::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete a service', function (): void {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        assertDatabaseMissing($record);
    });

    it('can bulk delete services', function (): void {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (Service $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
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
        '`address` is max 100 characters' => [['address' => Str::random(101)], ['address' => 'max']],
        '`email` must be valid' => [['email' => 'not-a-valid-email'], ['email' => 'email']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors): void {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
                ...$data,
            ])
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 100 characters' => [['name' => Str::random(101)], ['name' => 'max']],
    ]);
});

describe('form fields', function (): void {
    it('has form field', function (string $field): void {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists($field);
    })->with(['name', 'abbreviation', 'direction_id', 'employer_id', 'address', 'postal_code', 'city', 'email', 'phone', 'gsm', 'notes']);
});

describe('relationships', function (): void {
    it('belongs to a direction', function (): void {
        $direction = Direction::factory()->create();
        $service = Service::factory()->create(['direction_id' => $direction->id]);

        expect($service->direction->id)->toBe($direction->id);
    });

    it('belongs to an employer', function (): void {
        $employer = Employer::factory()->create();
        $service = Service::factory()->create(['employer_id' => $employer->id]);

        expect($service->employer->id)->toBe($employer->id);
    });
});
