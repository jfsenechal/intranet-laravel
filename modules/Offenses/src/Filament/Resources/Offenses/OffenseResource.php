<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses;

use AcMarche\Offenses\Filament\Resources\Offenses\Pages\CreateOffense;
use AcMarche\Offenses\Filament\Resources\Offenses\Pages\EditOffense;
use AcMarche\Offenses\Filament\Resources\Offenses\Pages\ListOffenses;
use AcMarche\Offenses\Filament\Resources\Offenses\Pages\ViewOffense;
use AcMarche\Offenses\Filament\Resources\Offenses\Schemas\OffenseForm;
use AcMarche\Offenses\Filament\Resources\Offenses\Schemas\OffenseInfolist;
use AcMarche\Offenses\Filament\Resources\Offenses\Tables\OffenseTables;
use AcMarche\Offenses\Models\Offense;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OffenseResource extends Resource
{
    #[Override]
    protected static ?string $model = Offense::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shield-exclamation';
    }

    public static function getNavigationLabel(): string
    {
        return 'Sanctions';
    }

    public static function form(Schema $schema): Schema
    {
        return OffenseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OffenseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffenseTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffenses::route('/'),
            'create' => CreateOffense::route('/create'),
            'view' => ViewOffense::route('/{record}/view'),
            'edit' => EditOffense::route('/{record}/edit'),
        ];
    }
}
