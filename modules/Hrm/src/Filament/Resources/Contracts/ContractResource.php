<?php

namespace AcMarche\Hrm\Filament\Resources\Contracts;

use AcMarche\Hrm\Filament\Resources\Contracts\Schemas\ContractForm;
use AcMarche\Hrm\Filament\Resources\Contracts\Tables\ContractTables;
use AcMarche\Hrm\Models\Contract;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static string|null|UnitEnum $navigationGroup = 'Personnel';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-duplicate';
    }

    public static function getNavigationLabel(): string
    {
        return 'Contrats';
    }

    public static function getModelLabel(): string
    {
        return 'Contrat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Contrats';
    }

    public static function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}/view'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
