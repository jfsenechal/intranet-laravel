<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Trainings;

use AcMarche\Hrm\Filament\Resources\Trainings\Pages\CreateTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\EditTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ListTrainings;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Schemas\TrainingForm;
use AcMarche\Hrm\Filament\Resources\Trainings\Schemas\TrainingInfolist;
use AcMarche\Hrm\Filament\Resources\Trainings\Tables\TrainingTables;
use AcMarche\Hrm\Models\Training;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class TrainingResource extends Resource
{
    #[Override]
    protected static ?string $model = Training::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 8;

    public static function getNavigationIcon(): string
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

    public static function infolist(Schema $schema): Schema
    {
        return TrainingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainings::route('/'),
            'create' => CreateTraining::route('/create'),
            'view' => ViewTraining::route('/{record}/view'),
            'edit' => EditTraining::route('/{record}/edit'),
        ];
    }
}
