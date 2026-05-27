<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\JobFunctions;

use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\CreateJobFunction;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\EditJobFunction;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\ListJobFunctions;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\ViewJobFunction;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Schemas\JobFunctionForm;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Schemas\JobFunctionInfoList;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Tables\JobFunctionsTable;
use AcMarche\Hrm\Models\JobFunction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class JobFunctionResource extends Resource
{
    #[Override]
    protected static ?string $model = JobFunction::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-briefcase';
    }

    public static function getNavigationLabel(): string
    {
        return 'Fonctions';
    }

    public static function getModelLabel(): string
    {
        return 'Fonction';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Fonctions';
    }

    public static function form(Schema $schema): Schema
    {
        return JobFunctionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobFunctionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobFunctionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobFunctions::route('/'),
            'create' => CreateJobFunction::route('/create'),
            'view' => ViewJobFunction::route('/{record}/view'),
            'edit' => EditJobFunction::route('/{record}/edit'),
        ];
    }
}
