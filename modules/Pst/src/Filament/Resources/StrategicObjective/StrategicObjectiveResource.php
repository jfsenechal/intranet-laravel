<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective;

use AcMarche\Pst\Filament\Concerns\InteractsWithScoutGlobalSearch;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\CreateStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\EditStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\ListStrategicObjectives;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\ViewStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\RelationManagers\OosRelationManager;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Schemas\StrategicObjectiveForm;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Tables\StrategicObjectiveTables;
use AcMarche\Pst\Models\StrategicObjective;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class StrategicObjectiveResource extends Resource
{
    use InteractsWithScoutGlobalSearch;

    #[Override]
    protected static ?string $model = StrategicObjective::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return 'Objectif Stratégique (OS)';
    }

    public static function form(Schema $schema): Schema
    {
        return StrategicObjectiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StrategicObjectiveTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStrategicObjectives::route('/'),
            'create' => CreateStrategicObjective::route('/create'),
            'view' => ViewStrategicObjective::route('/{record}'),
            'edit' => EditStrategicObjective::route('/{record}/edit'),
        ];
    }
}
