<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs;

use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\CreateOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\EditOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ListOffenseActs;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ViewOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Schemas\OffenseActForm;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Tables\OffenseActTables;
use AcMarche\Offenses\Models\OffenseAct;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OffenseActResource extends Resource
{
    #[Override]
    protected static ?string $model = OffenseAct::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationLabel(): string
    {
        return "Types d'actes";
    }

    public static function form(Schema $schema): Schema
    {
        return OffenseActForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffenseActTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffenseActs::route('/'),
            'create' => CreateOffenseAct::route('/create'),
            'view' => ViewOffenseAct::route('/{record}/view'),
            'edit' => EditOffenseAct::route('/{record}/edit'),
        ];
    }
}
