<?php

namespace AcMarche\Hrm\Filament\Resources\Employees;

use AcMarche\Hrm\Filament\Resources\Employees\Schemas\EmployeeForm;
use AcMarche\Hrm\Filament\Resources\Employees\Tables\EmployeeTables;
use AcMarche\Hrm\Models\Employee;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Personnel';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
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

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}/view'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
