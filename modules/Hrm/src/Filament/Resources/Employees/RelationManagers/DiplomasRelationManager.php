<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Diplomas\Schemas\DiplomaForm;
use AcMarche\Hrm\Filament\Resources\Diplomas\Schemas\DiplomaInfolist;
use AcMarche\Hrm\Filament\Resources\Diplomas\Tables\DiplomaTables;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\VisibleWhenEmployeeIsViewable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class DiplomasRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;
    use VisibleWhenEmployeeIsViewable;

    #[Override]
    protected static string $relationship = 'diplomas';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Diplômes';
    }

    public function form(Schema $schema): Schema
    {
        return DiplomaForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return DiplomaInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DiplomaTables::relation($table);
    }
}
