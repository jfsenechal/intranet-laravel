<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks;

use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\EditTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\HrValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ListTeleworks;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ManagerValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ViewTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Schemas\TeleworkForm;
use AcMarche\Hrm\Filament\Resources\Teleworks\Schemas\TeleworkInfolist;
use AcMarche\Hrm\Filament\Resources\Teleworks\Tables\TeleworkTables;
use AcMarche\Hrm\Models\Telework;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class TeleworkResource extends Resource
{
    #[Override]
    protected static ?string $model = Telework::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 9;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home-modern';
    }

    public static function getNavigationLabel(): string
    {
        return 'Télétravail';
    }

    public static function getModelLabel(): string
    {
        return 'Télétravail';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Télétravail';
    }

    public static function form(Schema $schema): Schema
    {
        return TeleworkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeleworkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeleworkTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeleworks::route('/'),
            'view' => ViewTelework::route('/{record}/view'),
            'edit' => EditTelework::route('/{record}/edit'),
            'manager-validate' => ManagerValidateTelework::route('/{record}/manager-validate'),
            'hr-validate' => HrValidateTelework::route('/{record}/hr-validate'),
        ];
    }
}
