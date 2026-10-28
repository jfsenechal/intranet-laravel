<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\ProcessExport;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\CreateProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\EditProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\ListProcesses;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\ViewProcess;
use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use AcMarche\Hrm\Models\Process;
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
        Livewire::test(ListProcesses::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateProcess::class)
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Process::factory()->create();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('view page', function (): void {
    it('shows the saved process with its actions', function (): void {
        $record = Process::factory()->create();

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee($record->name)
            ->assertActionVisible('backToList')
            ->assertActionVisible('edit')
            ->assertActionVisible('replicate')
            ->assertActionVisible('delete');
    });

    it('duplicates a process and opens the copy for editing', function (): void {
        $record = Process::factory()->create(['name' => 'Recrutement']);

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->callAction('replicate')
            ->assertHasNoActionErrors();

        $replica = Process::query()->where('name', 'Recrutement (copie)')->sole();

        expect($replica->description)->toBe($record->description)
            ->and($replica->id)->not->toBe($record->id);
    });

    it('does not copy the attachments to the duplicate', function (): void {
        $record = Process::factory()->create([
            'name' => 'Recrutement',
            'attachments' => ['hrm/processes/abc123.pdf'],
            'attachment_names' => ['hrm/processes/abc123.pdf' => 'organigramme.pdf'],
        ]);

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->callAction('replicate')
            ->assertHasNoActionErrors();

        $replica = Process::query()->where('name', 'Recrutement (copie)')->sole();

        expect($replica->attachments)->toBeNull()
            ->and($replica->attachment_names)->toBeNull()
            ->and($record->refresh()->attachments)->toBe(['hrm/processes/abc123.pdf']);
    });
});

describe('description', function (): void {
    it('saves the html written in the rich editor', function (): void {
        $record = Process::factory()->create();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'description' => '<p>Étape <strong>une</strong></p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($record->refresh()->description)->toContain('<strong>une</strong>');
    });

    it('renders the html description on the view page', function (): void {
        $record = Process::factory()->create(['description' => '<p>Étape <strong>une</strong></p>']);

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->assertSeeHtml('<strong>une</strong>');
    });

    it('keeps the line breaks of a description saved as plain text', function (): void {
        $record = Process::factory()->create(['description' => "Ligne un\nR & D"]);

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->assertSeeHtml('Ligne un<br />')
            ->assertSeeHtml('R &amp; D');
    });

    it('exports the description without its html tags', function (): void {
        Process::factory()->create(['name' => 'Recrutement', 'description' => '<p>Étape <strong>une</strong> &amp; deux</p>']);

        $component = Livewire::test(ListProcesses::class)
            ->loadTable()
            ->callAction('export', data: ['columns' => ['description']]);

        $rows = xlsxRows(base64_decode((string) data_get($component->effects, 'download.content')));

        expect($rows[1])->toBe(['Étape une & deux']);
    });
});

describe('attachments', function (): void {
    it('stores the uploaded files with their original names', function (): void {
        Storage::fake('local');
        $record = Process::factory()->create();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'attachments' => [UploadedFile::fake()->create('organigramme.pdf', 100, 'application/pdf')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $record->refresh();
        $path = $record->attachments[0];

        expect($record->attachments)->toHaveCount(1)
            ->and($path)->toStartWith(config('hrm.uploads.processes').'/')
            ->and($record->attachment_names[$path])->toBe('organigramme.pdf');

        Storage::disk('local')->assertExists($path);
    });

    it('lists the attachments under their original names on the view page', function (): void {
        Storage::fake('local');
        $record = Process::factory()->create([
            'attachments' => ['hrm/processes/abc123.pdf'],
            'attachment_names' => ['hrm/processes/abc123.pdf' => 'organigramme.pdf'],
        ]);

        Livewire::test(ViewProcess::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee('organigramme.pdf');
    });
});

describe('crud operations', function (): void {
    it('can create a process', function (): void {
        $newData = Process::factory()->make();

        Livewire::test(CreateProcess::class)
            ->fillForm([
                'name' => $newData->name,
                'description' => $newData->description,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect(ProcessResource::getUrl('view', [
                'record' => Process::query()->where('name', $newData->name)->sole(),
            ]));
    });

    it('can update a process', function (): void {
        $record = Process::factory()->create();
        $newData = Process::factory()->make();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect(ProcessResource::getUrl('view', ['record' => $record]));

        assertDatabaseHas(Process::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Process::factory()->make();

        Livewire::test(CreateProcess::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('table', function (): void {
    it('can search by name', function (): void {
        $records = Process::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListProcesses::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('export action', function (): void {
    it('exports the rows in the order the table is sorted', function (): void {
        Process::factory()->create(['name' => 'Bernard']);
        Process::factory()->create(['name' => 'Albert']);
        Process::factory()->create(['name' => 'Colin']);

        $component = Livewire::test(ListProcesses::class)
            ->loadTable()
            ->sortTable('name', 'desc')
            ->callAction('export', data: ['columns' => ['name']])
            ->assertHasNoActionErrors();

        $rows = xlsxRows(base64_decode((string) data_get($component->effects, 'download.content')));

        expect($rows)->toBe([['Nom'], ['Colin'], ['Bernard'], ['Albert']]);
    });

    it('renders the export action on the index page', function (): void {
        Livewire::test(ListProcesses::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Process::factory(2)->create();

        Livewire::test(ListProcesses::class)
            ->callAction('export', data: ['columns' => array_keys(ProcessExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Process::factory(2)->create();

        Livewire::test(ListProcesses::class)
            ->callAction('export', data: ['columns' => ['name', 'description']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListProcesses::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
