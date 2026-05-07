<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Override;

final class ListDayMeals extends Page implements HasTable
{
    use InteractsWithTable;

    public Week $record;

    public string $date;

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.list-day-meals';

    public function mount(Week $record, string $date): void
    {
        $this->record = $record;
        $this->date = CarbonImmutable::parse($date)->format('Y-m-d');
    }

    public function getTitle(): string
    {
        return 'Les commandes du '.CarbonImmutable::parse($this->date)->translatedFormat('l j F Y');
    }

    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]) => 'Semaine du '.$this->record->formattedFirstDay(),
            $this->getTitle(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->buildQuery())
            ->columns([
                TextColumn::make('client_name')
                    ->label('Client')
                    ->state(fn (Meal $record): string => mb_trim(
                        ($record->order?->client?->last_name ?? '').' '.($record->order?->client?->first_name ?? ''),
                    ))
                    ->sortable(['clients.last_name', 'clients.first_name']),

                IconColumn::make('at_cafeteria')
                    ->label('Cafétéria')
                    ->boolean()
                    ->summarize(
                        Count::make()
                            ->label('Total')
                            ->query(fn (QueryBuilder $query): QueryBuilder => $query->where('at_cafeteria', true)),
                    ),

                TextColumn::make('soup_count')
                    ->label('Potage')
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('menu_1_quantity')
                    ->label('Menu 1')
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('menu_2_quantity')
                    ->label('Menu 2')
                    ->summarize(Sum::make()->label('Total')),
            ])
            ->paginated(false)
            ->defaultSort('clients.last_name');
    }

    private function buildQuery(): Builder
    {
        return Meal::query()
            ->select('meals.*')
            ->join('orders', 'orders.id', '=', 'meals.order_id')
            ->leftJoin('clients', 'clients.id', '=', 'orders.client_id')
            ->whereDate('meals.date', $this->date)
            ->where('orders.week_id', $this->record->id)
            ->with('order.client')
            ->selectSub(
                Menu::query()
                    ->whereColumn('meal_id', 'meals.id')
                    ->where('position', 1)
                    ->selectRaw('coalesce(sum(quantity), 0)'),
                'menu_1_quantity',
            )
            ->selectSub(
                Menu::query()
                    ->whereColumn('meal_id', 'meals.id')
                    ->where('position', 2)
                    ->selectRaw('coalesce(sum(quantity), 0)'),
                'menu_2_quantity',
            );
    }
}
