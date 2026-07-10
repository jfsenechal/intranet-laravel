<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\Trainings\Schemas\TrainingForm;
use AcMarche\Hrm\Filament\Resources\Trainings\Schemas\TrainingInfolist;
use AcMarche\Hrm\Filament\Resources\Trainings\Tables\TrainingTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Override;

final class TrainingsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;

    #[Override]
    protected static string $relationship = 'trainings';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Formations';
    }

    public function form(Schema $schema): Schema
    {
        return TrainingForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return TrainingInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return TrainingTables::relation($table)
            ->description(new HtmlString(collect(TrainingTypeEnum::cases())
                ->map(fn (TrainingTypeEnum $case): string => '<p><strong>'.e($case->getLabel()).'</strong>: '.e($case->getDescription()).'</p>')
                ->implode('')));
    }
}
