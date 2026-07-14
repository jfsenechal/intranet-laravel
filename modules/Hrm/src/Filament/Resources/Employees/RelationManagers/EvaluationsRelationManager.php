<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\VisibleWhenEmployeeIsViewable;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationInfolist;
use AcMarche\Hrm\Filament\Resources\Evaluations\Tables\EvaluationTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EvaluationsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;
    use VisibleWhenEmployeeIsViewable;

    #[Override]
    protected static string $relationship = 'evaluations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Evaluations';
    }

    public function form(Schema $schema): Schema
    {
        return EvaluationForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return EvaluationInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return EvaluationTables::configure($table);
    }
}
