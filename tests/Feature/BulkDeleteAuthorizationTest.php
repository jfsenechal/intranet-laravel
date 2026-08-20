<?php

declare(strict_types=1);

use AcMarche\Ad\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\Ad\Models\Category;
use AcMarche\Courrier\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

/**
 * AppServiceProvider makes every DeleteBulkAction authorize record by record,
 * because Filament otherwise checks `deleteAny()` and allows the action
 * outright when a policy omits that method.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

test('every delete bulk action authorizes its records individually', function (): void {
    expect(DeleteBulkAction::make()->shouldAuthorizeIndividualRecords())->toBeTrue();
});

/**
 * Courrier services are the shape the gap exposed: the list is readable by
 * every authenticated user, while `delete()` is reserved to administrators.
 */
test('a user the policy refuses cannot bulk delete', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    $services = Service::factory()->count(2)->create();

    livewire(ListServices::class)
        ->loadTable()
        ->mountTableBulkAction('delete', $services)
        ->callMountedTableBulkAction();

    expect(Service::query()->whereKey($services->pluck('id'))->count())->toBe(2);
});

test('an authorized user still bulk deletes', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $services = Service::factory()->count(2)->create();

    livewire(ListServices::class)
        ->loadTable()
        ->mountTableBulkAction('delete', $services)
        ->callMountedTableBulkAction();

    expect(Service::query()->whereKey($services->pluck('id'))->count())->toBe(0);
});

/**
 * Ad categories carry no `deleteAny()` at all, so they rely entirely on the
 * global configuration: a public list, deletion reserved to the module role.
 * Eighteen policies across the application share this shape.
 */
test('a policy without deleteAny is still enforced on bulk delete', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('ad-panel'));
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    $categories = Category::factory()->count(2)->create();

    livewire(ListCategory::class)
        ->loadTable()
        ->mountTableBulkAction('delete', $categories)
        ->callMountedTableBulkAction();

    expect(Category::query()->whereKey($categories->pluck('id'))->count())->toBe(2);
});
