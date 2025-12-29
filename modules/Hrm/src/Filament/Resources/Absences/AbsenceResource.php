<?php

namespace AcMarche\Hrm\Filament\Resources\Absences;

use AcMarche\Hrm\Filament\Resources\Absences\Schemas\AbsenceForm;
use AcMarche\Hrm\Filament\Resources\Absences\Tables\AbsenceTables;
use AcMarche\Hrm\Models\Absence;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static ?string $navigationGroup = 'Personnel';

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): ?string
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
