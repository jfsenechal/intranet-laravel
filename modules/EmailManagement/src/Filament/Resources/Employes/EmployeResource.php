<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes;

use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\EditEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ListEmployes;
use AcMarche\EmailManagement\Filament\Resources\Employes\Pages\ViewEmploye;
use AcMarche\EmailManagement\Filament\Resources\Employes\Schemas\EmployeForm;
use AcMarche\EmailManagement\Filament\Resources\Employes\Schemas\EmployeInfolist;
use AcMarche\EmailManagement\Filament\Resources\Employes\Tables\EmployesTable;
use AcMarche\EmailManagement\Models\Employe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class EmployeResource extends Resource
{
    protected static ?string $model = Employe::class;

    #[Override]
    protected static ?string $navigationLabel = 'Employés';

    #[Override]
    protected static ?string $modelLabel = 'employé';

    #[Override]
    protected static ?string $pluralModelLabel = 'Employés';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EmployeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployes::route('/'),
            'view' => ViewEmploye::route('/{record}'),
            'edit' => EditEmploye::route('/{record}/edit'),
        ];
    }
}
