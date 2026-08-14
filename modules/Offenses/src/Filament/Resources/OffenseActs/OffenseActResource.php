<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs;

use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\CreateOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\EditOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ListOffenseActs;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ViewOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\RelationManagers\OffensesRelationManager;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Schemas\OffenseActForm;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Tables\OffenseActTables;
use AcMarche\Offenses\Models\OffenseAct;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class OffenseActResource extends Resource
{
    #[Override]
    protected static ?string $model = OffenseAct::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationLabel(): string
    {
        return "Types d'actes";
    }

    public static function form(Schema $schema): Schema
    {
        return OffenseActForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffenseActTables::configure($table);
    }

    /**
     * A used act cannot be deleted, so the modal says so instead of offering a delete button the
     * model would refuse anyway. Shared by the view and edit pages.
     */
    public static function deleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->icon(Heroicon::Trash)
            ->modalDescription(fn (OffenseAct $record): string => self::deletionBlockedMessage($record)
                ?? "Êtes-vous sûr de vouloir supprimer ce type d'acte ? Cette action est irréversible.")
            ->modalSubmitAction(fn (OffenseAct $record): ?bool => self::deletionBlockedMessage($record) === null ? null : false)
            ->failureNotificationTitle('Suppression impossible')
            ->failureNotificationBody(fn (OffenseAct $record): ?string => self::deletionBlockedMessage($record));
    }

    public static function deletionBlockedMessage(OffenseAct $record): ?string
    {
        $offenseCount = $record->offenses()->count();

        if ($offenseCount === 0) {
            return null;
        }

        return trans_choice(
            "{1} Ce type d'acte est utilisé par :count incivilité et ne peut pas être supprimé.|[2,*] Ce type d'acte est utilisé par :count incivilités et ne peut pas être supprimé.",
            $offenseCount,
            ['count' => $offenseCount],
        );
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
            'index' => ListOffenseActs::route('/'),
            'create' => CreateOffenseAct::route('/create'),
            'view' => ViewOffenseAct::route('/{record}/view'),
            'edit' => EditOffenseAct::route('/{record}/edit'),
        ];
    }
}
