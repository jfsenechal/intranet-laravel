<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Absences;

use AcMarche\MealDelivery\Filament\Resources\Absence\Schemas\AbsenceForm;
use AcMarche\MealDelivery\Filament\Resources\Absences\Pages\EditAbsence;
use AcMarche\MealDelivery\Filament\Resources\Absences\Pages\ListAbsences;
use AcMarche\MealDelivery\Filament\Resources\Absences\Tables\AbsenceTables;
use AcMarche\MealDelivery\Models\Absence;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class AbsenceResource extends Resource
{
    #[Override]
    protected static ?string $model = Absence::class;

    #[Override]
    protected static ?int $navigationSort = 7;

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::query()->count();
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-paper-airplane';
    }

    public static function getNavigationLabel(): string
    {
        return 'Liste des absences';
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
            'index' => ListAbsences::route('/'),
            'edit' => EditAbsence::route('/{record}/edit'),
        ];
    }
}
