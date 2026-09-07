<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas\GuestReservationForm;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Tables\GuestReservationTables;
use AcMarche\MealDelivery\Models\Resident;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class GuestReservationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'guestReservations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Repas invités ('.$ownerRecord->guestReservations()->count().')';
    }

    /**
     * The panel makes relation managers read-only on resource view pages, which
     * denies every CreateAction and EditAction; these reservations are meant to
     * stay bookable and editable straight from the resident's page.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        /** @var Resident $resident */
        $resident = $this->getOwnerRecord();

        return GuestReservationForm::configureForResident($schema, $resident);
    }

    public function table(Table $table): Table
    {
        return GuestReservationTables::inline($table)
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un repas invité')
                    ->icon('tabler-plus')
                    ->modalHeading('Nouveau repas invité')
                    ->modalSubmitActionLabel('Enregistrer'),
            ]);
    }
}
