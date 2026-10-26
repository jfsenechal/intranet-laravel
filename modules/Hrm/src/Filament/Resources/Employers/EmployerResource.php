<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers;

use AcMarche\Hrm\Filament\Resources\Employers\Pages\CreateEmployer;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\EditEmployer;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\ListEmployers;
use AcMarche\Hrm\Filament\Resources\Employers\Pages\ViewEmployer;
use AcMarche\Hrm\Filament\Resources\Employers\Schemas\EmployerForm;
use AcMarche\Hrm\Filament\Resources\Employers\Schemas\EmployerInfolist;
use AcMarche\Hrm\Filament\Resources\Employers\Tables\EmployerTables;
use AcMarche\Hrm\Models\Employer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class EmployerResource extends Resource
{
    #[Override]
    protected static ?string $model = Employer::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationLabel(): string
    {
        return 'Employeurs';
    }

    public static function getModelLabel(): string
    {
        return 'Employeur';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Employeurs';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployerTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployers::route('/'),
            'create' => CreateEmployer::route('/create'),
            'view' => ViewEmployer::route('/{record}/view'),
            'edit' => EditEmployer::route('/{record}/edit'),
        ];
    }
}
