<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\Partner\Pages\CreatePartner;
use AcMarche\Pst\Filament\Resources\Partner\Pages\EditPartner;
use AcMarche\Pst\Filament\Resources\Partner\Pages\ListPartners;
use AcMarche\Pst\Filament\Resources\Partner\Pages\ViewPartner;
use AcMarche\Pst\Models\Partner;
use AcMarche\Security\Models\Role;
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
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListPartners::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Partner::factory()->create();

        Livewire::test(ViewPartner::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Partner::factory()->create();

        Livewire::test(EditPartner::class, [
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
        Livewire::test(ListPartners::class)
            ->assertTableColumnExists($column);
    })->with(['name', 'initials', 'email', 'phone']);

    it('can render column', function (string $column): void {
        Partner::factory()->create();

        Livewire::test(ListPartners::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with(['name', 'email', 'phone']);

    it('can sort by name', function (): void {
        $records = Partner::factory(3)->create();

        Livewire::test(ListPartners::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function (): void {
        $records = Partner::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListPartners::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function (): void {
    it('can create a partner', function (): void {
        $newData = Partner::factory()->make();

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'name' => $newData->name,
                'email' => 'test@example.com',
                'phone' => '+32123456789',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Partner::class, [
            'name' => $newData->name,
            'email' => 'test@example.com',
        ]);
    });

    it('can update a partner', function (): void {
        $record = Partner::factory()->create();
        $newData = Partner::factory()->make();

        Livewire::test(EditPartner::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Partner::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete a partner', function (): void {
        $record = Partner::factory()->create();

        Livewire::test(ViewPartner::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

    it('can bulk delete partners', function (): void {
        $records = Partner::factory(3)->create();

        Livewire::test(ListPartners::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (Partner $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Partner::factory()->make();

        Livewire::test(CreatePartner::class)
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
        '`email` must be valid email' => [['email' => 'invalid-email'], ['email' => 'email']],
        '`initials` is max 30 characters' => [['initials' => Str::random(31)], ['initials' => 'max']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors): void {
        $record = Partner::factory()->create();

        Livewire::test(EditPartner::class, [
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
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`email` must be valid email' => [['email' => 'invalid-email'], ['email' => 'email']],
    ]);
});

describe('form fields', function (): void {
    it('has name field', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertFormFieldExists('name');
    });

    it('has initials field', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertFormFieldExists('initials');
    });

    it('has email field', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertFormFieldExists('email');
    });

    it('has phone field', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertFormFieldExists('phone');
    });

    it('has description field', function (): void {
        Livewire::test(CreatePartner::class)
            ->assertFormFieldExists('description');
    });
});
