<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Deadlines\Schemas\DeadlineForm;
use AcMarche\Hrm\Filament\Resources\Deadlines\Schemas\DeadlineInfolist;
use AcMarche\Hrm\Filament\Resources\Deadlines\Tables\DeadlineTables;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class DeadlinesRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;

    #[Override]
    protected static string $relationship = 'deadlines';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Echéances';
    }

    public function form(Schema $schema): Schema
    {
        return DeadlineForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return DeadlineInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DeadlineTables::relation($table);
    }
}
