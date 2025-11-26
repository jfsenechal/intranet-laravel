<?php

namespace AcMarche\Mileage\Filament\Resources;

use AcMarche\Mileage\Filament\Resources\RateResource\Pages;
use AcMarche\Mileage\Filament\Resources\RateResource\Schema\RateForm;
use AcMarche\Mileage\Filament\Resources\RateResource\Tables\RateTables;
use AcMarche\Mileage\Models\Rate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RateResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Mileage';

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): ?string
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
            'index' => Pages\ListRates::route('/'),
            'create' => Pages\CreateRate::route('/create'),
            'edit' => Pages\EditRate::route('/{record}/edit'),
        ];
    }
}
