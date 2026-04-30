<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences;

use AcMarche\Hrm\Filament\Resources\Absences\Schemas\AbsenceForm;
use AcMarche\Hrm\Filament\Resources\Absences\Schemas\AbsenceInfolist;
use AcMarche\Hrm\Filament\Resources\Absences\Tables\AbsenceTables;
use AcMarche\Hrm\Models\Absence;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class AbsenceResource extends Resource
{
    #[Override]
    protected static ?string $model = Absence::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return 'Absences';
    }

    public static function getModelLabel(): string
    {
        return 'Absence';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Absences';
    }

    public static function form(Schema $schema): Schema
    {
        return AbsenceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AbsenceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsenceTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'view' => Pages\ViewAbsence::route('/{record}/view'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
        ];
    }
}
