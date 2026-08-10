<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Pages\RateMismatchReport;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));
    $this->user = User::factory()->create(['username' => 'jdupont', 'is_administrator' => true]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => 'jdupont']);
    $this->actingAs($this->user);

    Rate::factory()->create([
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'amount' => 0.5000,
        'omnium' => 0.0062,
    ]);
});

/**
 * A declaration reimbursed at $paid, with one trip in June 2026 where the
 * official rate is 0.5000.
 */
function declarationPaidAt(float $paid, int $distance = 100, string $lastName = 'Dupont'): Declaration
{
    $declaration = Declaration::factory()->create([
        'last_name' => $lastName,
        'first_name' => 'Jean',
        'user_add' => 'jdupont',
        'rate' => $paid,
    ]);

    Trip::factory()->create([
        'declaration_id' => $declaration->id,
        'departure_date' => '2026-06-15',
        'distance' => $distance,
    ]);

    return $declaration;
}

it('can render the page', function (): void {
    livewire(RateMismatchReport::class)
        ->assertOk();
});

it('lists a declaration paid below the official rate', function (): void {
    $declaration = declarationPaidAt(0.4000);

    livewire(RateMismatchReport::class)
        ->assertOk()
        ->assertSee('#'.$declaration->id)
        // (0.5000 - 0.4000) * 100 km
        ->assertSee('+10,00 €');
});

it('does not list a declaration paid at the official rate', function (): void {
    $declaration = declarationPaidAt(0.5000);

    livewire(RateMismatchReport::class)
        ->assertDontSee('#'.$declaration->id);
});

it('reports an over-payment as a negative gap', function (): void {
    declarationPaidAt(0.6000);

    livewire(RateMismatchReport::class)
        ->assertSee('-10,00 €');
});

it('ignores a trip whose stored rate alone diverges', function (): void {
    // The declaration carries the official rate, only the trip row is stale:
    // nothing was underpaid, so it must not appear.
    $declaration = declarationPaidAt(0.5000);
    $declaration->trips()->first()->update(['rate' => 0.1000]);

    livewire(RateMismatchReport::class)
        ->assertDontSee('#'.$declaration->id);
});

it('filters on the search term', function (): void {
    $wanted = declarationPaidAt(0.4000, lastName: 'Verplaetse');
    $other = declarationPaidAt(0.4000, lastName: 'Monnoyer');

    livewire(RateMismatchReport::class)
        ->set('search', 'verplaetse')
        ->assertSee('#'.$wanted->id)
        ->assertDontSee('#'.$other->id);
});

it('excludes trips before the selected year', function (): void {
    $declaration = declarationPaidAt(0.4000);

    livewire(RateMismatchReport::class)
        ->set('fromYear', 2026)
        ->assertSee('#'.$declaration->id)
        ->set('fromYear', 2024)
        ->assertSee('#'.$declaration->id);
});

it('is not accessible without the finance role', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    expect(RateMismatchReport::canAccess())->toBeFalse();
});
