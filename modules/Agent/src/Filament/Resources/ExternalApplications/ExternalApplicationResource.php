<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\ExternalApplications;

use AcMarche\Agent\Filament\Resources\ExternalApplications\Pages\CreateExternalApplication;
use AcMarche\Agent\Filament\Resources\ExternalApplications\Pages\EditExternalApplication;
use AcMarche\Agent\Filament\Resources\ExternalApplications\Pages\ListExternalApplications;
use AcMarche\Agent\Filament\Resources\ExternalApplications\Schemas\ExternalApplicationForm;
use AcMarche\Agent\Filament\Resources\ExternalApplications\Tables\ExternalApplicationTables;
use AcMarche\Agent\Models\ExternalApplication;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ExternalApplicationResource extends Resource
{
    #[Override]
    protected static ?string $model = ExternalApplication::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-puzzle-piece';
    }

    public static function getNavigationLabel(): string
    {
        return 'Applications externes';
    }

    public static function getModelLabel(): string
    {
        return 'application externe';
    }

    public static function getPluralModelLabel(): string
    {
        return 'applications externes';
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalApplicationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalApplications::route('/'),
            'create' => CreateExternalApplication::route('/create'),
            'edit' => EditExternalApplication::route('/{record}/edit'),
        ];
    }
}
