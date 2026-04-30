<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services;

use AcMarche\Hrm\Filament\Resources\Services\Pages\CreateService;
use AcMarche\Hrm\Filament\Resources\Services\Pages\EditService;
use AcMarche\Hrm\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Hrm\Filament\Resources\Services\Pages\ViewService;
use AcMarche\Hrm\Filament\Resources\Services\Schemas\ServiceForm;
use AcMarche\Hrm\Filament\Resources\Services\Schemas\ServiceInfolist;
use AcMarche\Hrm\Filament\Resources\Services\Tables\ServiceTables;
use AcMarche\Hrm\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ServiceResource extends Resource
{
    #[Override]
    protected static ?string $model = Service::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 8;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-library';
    }

    public static function getNavigationLabel(): string
    {
        return 'Services';
    }

    public static function getModelLabel(): string
    {
        return 'Service';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Services';
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
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
            'view' => ViewService::route('/{record}/view'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
