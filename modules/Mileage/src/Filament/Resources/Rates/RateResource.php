<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Rates;

use AcMarche\Mileage\Filament\Resources\Rates\Pages\CreateRate;
use AcMarche\Mileage\Filament\Resources\Rates\Pages\EditRate;
use AcMarche\Mileage\Filament\Resources\Rates\Pages\ListRates;
use AcMarche\Mileage\Filament\Resources\Rates\Schemas\RateForm;
use AcMarche\Mileage\Filament\Resources\Rates\Tables\RateTables;
use AcMarche\Mileage\Models\Rate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class RateResource extends Resource
{
    #[Override]
    protected static ?string $model = Rate::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

    #[Override]
    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-currency-euro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tarifs';
    }

    public static function form(Schema $schema): Schema
    {
        return RateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RateTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRates::route('/'),
            'create' => CreateRate::route('/create'),
            'edit' => EditRate::route('/{record}/edit'),
        ];
    }
}
