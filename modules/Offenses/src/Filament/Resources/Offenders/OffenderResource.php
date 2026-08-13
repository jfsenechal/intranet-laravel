<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders;

use AcMarche\Offenses\Filament\Resources\Offenders\Pages\CreateOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\EditOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\ListOffenders;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\ViewOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\RelationManagers\OffensesRelationManager;
use AcMarche\Offenses\Filament\Resources\Offenders\Schemas\OffenderForm;
use AcMarche\Offenses\Filament\Resources\Offenders\Schemas\OffenderInfolist;
use AcMarche\Offenses\Filament\Resources\Offenders\Tables\OffenderTables;
use AcMarche\Offenses\Models\Offender;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OffenderResource extends Resource
{
    #[Override]
    protected static ?string $model = Offender::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationLabel(): string
    {
        return 'Contrevenants';
    }

    public static function form(Schema $schema): Schema
    {
        return OffenderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OffenderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffenderTables::configure($table);
    }

    /**
     * Deleting an offender cascades onto its offenses and their uploaded files, so the
     * confirmation modal has to say how much is about to go.
     */
    public static function deletionWarning(int $offenseCount): string
    {
        if ($offenseCount === 0) {
            return 'Êtes-vous sûr de vouloir supprimer ce contrevenant ? Cette action est irréversible.';
        }

        return trans_choice(
            '{1} :count incivilité et son fichier joint seront également supprimés.|[2,*] :count incivilités et leurs fichiers joints seront également supprimés.',
            $offenseCount,
            ['count' => $offenseCount],
        ).' Cette action est irréversible.';
    }

    public static function getRelations(): array
    {
        return [
            OffensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffenders::route('/'),
            'create' => CreateOffender::route('/create'),
            'view' => ViewOffender::route('/{record}/view'),
            'edit' => EditOffender::route('/{record}/edit'),
        ];
    }
}
