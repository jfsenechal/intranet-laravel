<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations;

use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\CreateEvaluation;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\EditEvaluation;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ListEvaluations;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationInfolist;
use AcMarche\Hrm\Filament\Resources\Evaluations\Tables\EvaluationTables;
use AcMarche\Hrm\Models\Evaluation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class EvaluationResource extends Resource
{
    #[Override]
    protected static ?string $model = Evaluation::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Personnel';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $recordTitleAttribute = 'employer_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-currency-euro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Valorisations';
    }

    public static function getModelLabel(): string
    {
        return 'Valorisation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Valorisations';
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvaluations::route('/'),
            'create' => CreateEvaluation::route('/create'),
            'view' => ViewEvaluation::route('/{record}/view'),
            'edit' => EditEvaluation::route('/{record}/edit'),
        ];
    }
}
