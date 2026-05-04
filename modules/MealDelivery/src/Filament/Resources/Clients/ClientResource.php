<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients;

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\CreateClient;
use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\EditClient;
use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ListClients;
use AcMarche\MealDelivery\Filament\Resources\Clients\Schemas\ClientForm;
use AcMarche\MealDelivery\Filament\Resources\Clients\Tables\ClientTables;
use AcMarche\MealDelivery\Models\Client;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ClientResource extends Resource
{
    #[Override]
    protected static ?string $model = Client::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'CPAS Repas';

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return 'Clients';
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
