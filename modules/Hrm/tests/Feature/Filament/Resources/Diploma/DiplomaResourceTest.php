<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\DiplomaExport;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\EditDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ListDiplomas;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ViewDiploma;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Livewire::test(ListDiplomas::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Diploma::factory()->create();

        Livewire::test(ViewDiploma::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Diploma::factory()->create();

        Livewire::test(EditDiploma::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a diploma', function (): void {
        $record = Diploma::factory()->create();
        $newData = Diploma::factory()->make();

        Livewire::test(EditDiploma::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Diploma::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('creating a diploma for an employee', function (): void {
    it('creates a diploma through the employee action with the employee_id set', function (): void {
        $employee = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $employee->id])
            ->callAction('addDiploma', ['name' => 'Diplôme aide-soignante'])
            ->assertHasNoActionErrors();

        assertDatabaseHas(Diploma::class, [
            'name' => 'Diplôme aide-soignante',
            'employee_id' => $employee->id,
        ]);
    });

    it('validates the form data on create', function (array $data, array $errors): void {
        $employee = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $employee->id])
            ->callAction('addDiploma', [
                'name' => 'Intitulé',
                ...$data,
            ])
            ->assertHasActionErrors($errors);
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 150 characters' => [['name' => Str::random(151)], ['name' => 'max']],
    ]);
});

describe('file upload storage', function (): void {
    it('stores the certificate on the private local disk, not the public disk', function (): void {
        Storage::fake('local');
        Storage::fake('public');

        $record = Diploma::factory()->create(['certificate_file' => null]);

        Livewire::test(EditDiploma::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'certificate_file' => UploadedFile::fake()->create('attestation.pdf', 100),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = $record->refresh()->certificate_file;

        expect($path)->not->toBeNull();
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListDiplomas::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Diploma::factory(2)->create();

        Livewire::test(ListDiplomas::class)
            ->callAction('export', data: ['columns' => array_keys(DiplomaExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Diploma::factory(2)->create();

        Livewire::test(ListDiplomas::class)
            ->callAction('export', data: ['columns' => ['agent', 'name']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListDiplomas::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
