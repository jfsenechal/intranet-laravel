<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees;

use AcMarche\Hrm\Filament\Resources\Employees\Pages\CreateEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\EditEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ListEmployees;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\AbsencesRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ApplicationsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ContractsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DeadlinesRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DiplomasRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DocumentsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\EvaluationsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\InternshipsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\TrainingsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ValorizationsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\Schemas\EmployeeForm;
use AcMarche\Hrm\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use AcMarche\Hrm\Filament\Resources\Employees\Tables\EmployeeTables;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

final class EmployeeResource extends Resource
{
    #[Override]
    protected static ?string $model = Employee::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Personnel';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $recordTitleAttribute = 'last_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return 'Agents';
    }

    public static function getModelLabel(): string
    {
        return 'Agent';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Agents';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'last_name',
            'first_name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->last_name.' '.$record->first_name;
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AbsencesRelationManager::class,
            ApplicationsRelationManager::class,
            ContractsRelationManager::class,
            DeadlinesRelationManager::class,
            DiplomasRelationManager::class,
            DocumentsRelationManager::class,
            EvaluationsRelationManager::class,
            TrainingsRelationManager::class,
            InternshipsRelationManager::class,
            ValorizationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}/view'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
