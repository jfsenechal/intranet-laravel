<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Weeks;

use AcMarche\CpasRepas\Filament\Resources\Weeks\Pages\CreateWeek;
use AcMarche\CpasRepas\Filament\Resources\Weeks\Pages\EditWeek;
use AcMarche\CpasRepas\Filament\Resources\Weeks\Pages\ListWeeks;
use AcMarche\CpasRepas\Filament\Resources\Weeks\Schemas\WeekForm;
use AcMarche\CpasRepas\Filament\Resources\Weeks\Tables\WeekTables;
use AcMarche\CpasRepas\Models\Week;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class WeekResource extends Resource
{
    #[Override]
    protected static ?string $model = Week::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'CPAS Repas';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return 'Weeks';
    }

    public static function form(Schema $schema): Schema
    {
        return WeekForm::configure($schema);
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
        ];
    }
}
