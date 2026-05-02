<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Diets;

use AcMarche\CpasRepas\Filament\Resources\Diets\Pages\CreateDiet;
use AcMarche\CpasRepas\Filament\Resources\Diets\Pages\EditDiet;
use AcMarche\CpasRepas\Filament\Resources\Diets\Pages\ListDiets;
use AcMarche\CpasRepas\Filament\Resources\Diets\Schemas\DietForm;
use AcMarche\CpasRepas\Filament\Resources\Diets\Tables\DietTables;
use AcMarche\CpasRepas\Models\Diet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class DietResource extends Resource
{
    #[Override]
    protected static ?string $model = Diet::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'CPAS Repas';

    #[Override]
    protected static ?int $navigationSort = 6;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationLabel(): string
    {
        return 'Diets';
    }

    public static function form(Schema $schema): Schema
    {
        return DietForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DietTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiets::route('/'),
            'create' => CreateDiet::route('/create'),
            'edit' => EditDiet::route('/{record}/edit'),
        ];
    }
}
