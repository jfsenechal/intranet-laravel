<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\OpensRecordViewPage;
use AcMarche\Hrm\Filament\Resources\Employees\Tables\EmployeeTables;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Employees currently working directly for the employer; employees of its
 * children are not listed. The `employees` relation goes through every
 * contract, so the table queries the employees directly: one row per person,
 * active contracts only.
 */
final class EmployeesRelationManager extends RelationManager
{
    use OpensRecordViewPage;

    #[Override]
    protected static string $relationship = 'employees';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Agents';
    }

    public static function getModelLabel(): ?string
    {
        return 'agent';
    }

    public static function getPluralModelLabel(): ?string
    {
        return 'agents';
    }

    public function table(Table $table): Table
    {
        return $this->openRecordsOnViewPage(
            EmployeeTables::relation($table)
                ->query(fn (): Builder => Employee::query()->whereHas(
                    'activeContracts',
                    fn (Builder $query): Builder => $query->where(
                        'employer_id',
                        $this->getOwnerRecord()->getKey(),
                    ),
                )),
            ViewEmployee::class,
        );
    }
}
