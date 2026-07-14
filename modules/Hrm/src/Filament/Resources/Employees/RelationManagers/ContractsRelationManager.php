<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Contracts\Schemas\ContractForm;
use AcMarche\Hrm\Filament\Resources\Contracts\Schemas\ContractInfolist;
use AcMarche\Hrm\Filament\Resources\Contracts\Tables\ContractTables;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\VisibleWhenEmployeeIsViewable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ContractsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;
    use VisibleWhenEmployeeIsViewable;

    #[Override]
    protected static string $relationship = 'contracts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Contrats';
    }

    public function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return ContractInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ContractTables::relation($table);
    }
}
