<?php

namespace AcMarche\Hrm\Filament\Resources\Trainings;

use AcMarche\Hrm\Filament\Resources\Trainings\Schemas\TrainingForm;
use AcMarche\Hrm\Filament\Resources\Trainings\Tables\TrainingTables;
use AcMarche\Hrm\Models\Training;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Personnel';

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationLabel(): string
    {
        return 'Formations';
    }

    public static function getModelLabel(): string
    {
        return 'Formation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Formations';
    }

    public static function form(Schema $schema): Schema
    {
        return TrainingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'view' => Pages\ViewTraining::route('/{record}/view'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }
}
