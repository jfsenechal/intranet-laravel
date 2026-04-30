<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts;

use AcMarche\Hrm\Filament\Resources\Contracts\Pages\CreateContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\EditContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ListContracts;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Schemas\ContractForm;
use AcMarche\Hrm\Filament\Resources\Contracts\Schemas\ContractInfolist;
use AcMarche\Hrm\Filament\Resources\Contracts\Tables\ContractTables;
use AcMarche\Hrm\Models\Contract;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ContractResource extends Resource
{
    #[Override]
    protected static ?string $model = Contract::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
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

    public static function infolist(Schema $schema): Schema
    {
        return ContractInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContracts::route('/'),
            'create' => CreateContract::route('/create'),
            'view' => ViewContract::route('/{record}/view'),
            'edit' => EditContract::route('/{record}/edit'),
        ];
    }
}
