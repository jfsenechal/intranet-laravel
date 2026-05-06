<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks;

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\CreateWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\EditWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ListDayMeals;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ListWeeks;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ViewWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Schemas\WeekForm;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Tables\WeekInfoList;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Tables\WeekTables;
use AcMarche\MealDelivery\Models\Week;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class WeekResource extends Resource
{
    #[Override]
    protected static ?string $model = Week::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return 'Semaines';
    }

    public static function form(Schema $schema): Schema
    {
        return WeekForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeekInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeekTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWeeks::route('/'),
            'create' => CreateWeek::route('/create'),
            'edit' => EditWeek::route('/{record}/edit'),
            'view' => ViewWeek::route('/{record}/view'),
            'day' => ListDayMeals::route('/{record}/day/{date}'),
        ];
    }
}
