<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents;

use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\CreateResident;
use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\EditResident;
use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\ListResidents;
use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\ViewResident;
use AcMarche\MealDelivery\Filament\Resources\Residents\RelationManagers\GuestReservationsRelationManager;
use AcMarche\MealDelivery\Filament\Resources\Residents\Schemas\ResidentForm;
use AcMarche\MealDelivery\Filament\Resources\Residents\Schemas\ResidentInfoList;
use AcMarche\MealDelivery\Filament\Resources\Residents\Tables\ResidentTables;
use AcMarche\MealDelivery\Models\Resident;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ResidentResource extends Resource
{
    #[Override]
    protected static ?string $model = Resident::class;

    #[Override]
    protected static ?int $navigationSort = 7;

    protected static string|UnitEnum|null $navigationGroup = 'Invités';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home-modern';
    }

    public static function getNavigationLabel(): string
    {
        return 'Résidents';
    }

    public static function form(Schema $schema): Schema
    {
        return ResidentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResidentInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResidentTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            GuestReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResidents::route('/'),
            'create' => CreateResident::route('/create'),
            'edit' => EditResident::route('/{record}/edit'),
            'view' => ViewResident::route('/{record}/view'),
        ];
    }
}
