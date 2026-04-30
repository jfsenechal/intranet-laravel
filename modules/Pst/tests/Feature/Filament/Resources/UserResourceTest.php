<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Pst\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

it('can render the index page', function (): void {
    Livewire::test(ListUsers::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'departments' => $user->departments,
        ]);
});

it('has column', function (string $column): void {
    Livewire::test(ListUsers::class)
        ->assertTableColumnExists($column);
})->with(['first_name', 'last_name', 'email', 'created_at']);

it('can render column', function (string $column): void {
    Livewire::test(ListUsers::class)
        ->loadTable()
        ->assertCanRenderTableColumn($column);
})->with(['first_name', 'last_name', 'email']);

it('can sort column', function (string $column): void {
    $records = User::factory(5)->create();

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['last_name']);

it('can search column', function (string $column): void {
    $records = User::factory(5)->create();

    $value = $records->first()->{$column};

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['last_name']);

it('can update a user', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'departments' => [DepartmentEnum::CPAS->value],
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'departments' => json_encode([DepartmentEnum::CPAS->value]),
    ]);
});

it('validates the form data', function (array $data, array $errors): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'departments' => [DepartmentEnum::VILLE->value],
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`departments` is required' => [['departments' => null], ['departments' => 'required']],
]);
