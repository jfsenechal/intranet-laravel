<?php

namespace AcMarche\Courrier\Filament\Resources\Services;

use AcMarche\Courrier\Filament\Resources\Services\Pages\CreateService;
use AcMarche\Courrier\Filament\Resources\Services\Pages\EditService;
use AcMarche\Courrier\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Courrier\Filament\Resources\Services\Pages\ViewService;
use AcMarche\Courrier\Filament\Resources\Services\Schemas\ServiceForm;
use AcMarche\Courrier\Filament\Resources\Services\Tables\ServiceTables;
use AcMarche\Courrier\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?int $navigationSort = 5;

    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationLabel(): string
    {
        return 'Services';
    }

    public static function getModelLabel(): string
    {
        return 'service';
    }

    public static function getPluralModelLabel(): string
    {
        return 'services';
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
