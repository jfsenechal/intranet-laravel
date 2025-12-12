<?php

namespace AcMarche\Courrier\Filament\Resources;

use AcMarche\Courrier\Filament\Resources\ServiceResource\Pages;
use AcMarche\Courrier\Filament\Resources\ServiceResource\Schema\ServiceForm;
use AcMarche\Courrier\Filament\Resources\ServiceResource\Tables\ServiceTables;
use AcMarche\Courrier\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?int $navigationSort = 2;

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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
