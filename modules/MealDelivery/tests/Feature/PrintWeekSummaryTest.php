<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\PrintWeekSummary;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ViewWeek;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15', '2026-06-16'],
    ]);

    $client = Client::create([
        'last_name' => 'BOON',
        'first_name' => 'Micheline',
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);

    $order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $client->id,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 3,
        'order_id' => $order->id,
    ]);

    Menu::create(['position' => 1, 'quantity' => 2, 'meal_id' => $meal->id]);
    Menu::create(['position' => 2, 'quantity' => 5, 'meal_id' => $meal->id]);
});

it('renders the week summary with only the counts columns', function (): void {
    livewire(PrintWeekSummary::class, ['record' => $this->week])
        ->assertOk()
        ->assertSee('Lundi 15 Juin 2026')
        ->assertSee('Mardi 16 Juin 2026')
        ->assertSee('Potages')
        ->assertDontSee('Export cuisine')
        ->assertDontSee('Feuilles de route')
        ->assertDontSee('Cafétariat');
});

it('totals the counts of every day of the week', function (): void {
    $page = livewire(PrintWeekSummary::class, ['record' => $this->week]);

    expect($page->instance()->getTotals())->toBe([
        'clients_count' => 1,
        'soup_count' => 3,
        'menu1_count' => 2,
        'menu2_count' => 5,
    ]);
});

it('links to the print page from the week view', function (): void {
    livewire(ViewWeek::class, ['record' => $this->week->id])
        ->assertOk()
        ->assertActionHasUrl('print', PrintWeekSummary::getUrl(['record' => $this->week->id]));
});

it('streams a landscape pdf of the summary when downloading', function (): void {
    Pdf::fake();

    livewire(PrintWeekSummary::class, ['record' => $this->week])
        ->call('downloadPdf')
        ->assertFileDownloaded('semaine-2026-06-15.pdf');

    Pdf::assertRespondedWithPdf(fn (PdfBuilder $pdf): bool => $pdf->viewName === 'meal-delivery::filament.resources.weeks.pages.print-week-summary-pdf'
        && $pdf->orientation === Orientation::Landscape->value
        && $pdf->viewData['totals'] === [
            'clients_count' => 1,
            'soup_count' => 3,
            'menu1_count' => 2,
            'menu2_count' => 5,
        ]
        && count($pdf->viewData['days']) === 2);
});

it('renders the pdf view with the days and their totals', function (): void {
    $page = livewire(PrintWeekSummary::class, ['record' => $this->week])->instance();

    $html = view('meal-delivery::filament.resources.weeks.pages.print-week-summary-pdf', [
        'heading' => $page->getTitle(),
        'days' => $page->days,
        'totals' => $page->getTotals(),
    ])->render();

    expect($html)->toContain('Lundi 15 Juin 2026')
        ->toContain('Mardi 16 Juin 2026')
        ->toContain('Potages')
        ->not->toContain('Export cuisine');
});
