<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('redirects to the monthly orders page for the selected month and year', function (): void {
    $client = createMealDeliveryClient('Export', isActive: true);

    livewire(ViewClient::class, ['record' => $client->id])
        ->callAction(TestAction::make('exportMonthlyOrders'), [
            'month' => 3,
            'year' => 2025,
        ])
        ->assertHasNoActionErrors()
        ->assertRedirect(ClientResource::getUrl('monthly-orders', [
            'record' => $client->id,
            'month' => 3,
            'year' => 2025,
        ]));
});
