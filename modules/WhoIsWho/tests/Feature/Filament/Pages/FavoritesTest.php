<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Filament\Pages\Favorites;
use AcMarche\WhoIsWho\Filament\Pages\Index;
use AcMarche\WhoIsWho\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\WhoIsWho\Models\FavoriteEmployee;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('who-is-who-panel'));

    $this->user = User::factory()->create(['is_administrator' => false]);
    $this->actingAs($this->user);
});

/**
 * Directory entries need an active contract to show up at all. Named apart from
 * the `activeAgent()` helper of ViewEmployeeTest: Pest declares test helpers in
 * the global namespace, so two files may not share a name.
 *
 * @param  array<string, mixed>  $attributes
 */
function favoriteTestAgent(array $attributes = []): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
        ...$attributes,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    return $employee;
}

it('adds an agent to the favorites from the directory', function (): void {
    $employee = favoriteTestAgent();

    livewire(Index::class)
        ->call('toggleFavoriteEmployee', $employee->id)
        ->assertOk();

    expect($this->user->refresh()->hasFavoriteEmployee($employee->id))->toBeTrue();

    $this->assertDatabaseHas(FavoriteEmployee::class, [
        'user_id' => $this->user->id,
        'employee_id' => $employee->id,
    ]);
});

it('removes an agent already in the favorites', function (): void {
    $employee = favoriteTestAgent();
    $this->user->toggleFavoriteEmployee($employee->id);

    livewire(Index::class)
        ->call('toggleFavoriteEmployee', $employee->id)
        ->assertOk();

    $this->assertDatabaseMissing(FavoriteEmployee::class, [
        'user_id' => $this->user->id,
        'employee_id' => $employee->id,
    ]);
});

it('toggles the favorite from the agent page', function (): void {
    $employee = favoriteTestAgent();

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertSee('Ajouter à mes favoris')
        ->callAction('toggleFavorite');

    expect($this->user->refresh()->hasFavoriteEmployee($employee->id))->toBeTrue();

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertSee('Retirer de mes favoris');
});

it('lists only the favorites of the current user', function (): void {
    $favorite = favoriteTestAgent(['last_name' => 'Favori']);
    $other = favoriteTestAgent(['last_name' => 'Ignoré']);

    $this->user->toggleFavoriteEmployee($favorite->id);

    FavoriteEmployee::query()->create([
        'user_id' => User::factory()->create()->id,
        'employee_id' => $other->id,
    ]);

    livewire(Favorites::class)
        ->assertOk()
        ->assertSee('Favori')
        ->assertDontSee('Ignoré');
});

it('keeps a favorite out of the list once the agent left the directory', function (): void {
    $employee = favoriteTestAgent(['last_name' => 'Parti']);
    $this->user->toggleFavoriteEmployee($employee->id);

    $employee->update(['is_archived' => true]);

    livewire(Favorites::class)
        ->assertOk()
        ->assertDontSee('Parti');
});
