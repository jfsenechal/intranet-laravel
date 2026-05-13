<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Actions\ExportMonthlyOrdersAction;
use AcMarche\MealDelivery\Filament\Resources\Absence\Schemas\AbsenceForm;
use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Clients\Schemas\ClientInfoList;
use AcMarche\MealDelivery\Filament\Resources\Notes\Schemas\NoteForm;
use AcMarche\MealDelivery\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Override;

final class ViewClient extends ViewRecord
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return $this->record->salutation.' '.$this->record->last_name.' '.$this->record->first_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ClientInfoList::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportMonthlyOrdersAction::make(),
            EditAction::make()
                ->icon('tabler-edit'),
            ActionGroup::make([
                ActionGroup::make([
                    CreateAction::make('addNote')
                        ->label('Ajouter une note')
                        ->icon('tabler-plus')
                        ->color('success')
                        ->modal()
                        ->createAnother(false)
                        ->schema(fn (Schema $schema) => NoteForm::configure($schema))
                        ->action(function (array $data, Client $record): void {
                            $record->notes()->create($data);
                        }),
                    CreateAction::make('addAbsence')
                        ->label('Ajouter une absence')
                        ->icon('tabler-plus')
                        ->color('warning')
                        ->visible(fn (Client $record): bool => $record->absence === null)
                        ->modal()
                        ->createAnother(false)
                        ->schema(fn (Schema $schema) => AbsenceForm::configure($schema))
                        ->action(function (array $data, Client $record): void {
                            $record->absence()->create($data);
                        }),

                ])->dropdown(false),
                Action::make('editAbsence')
                    ->label('Modifier l\'absence')
                    ->icon('tabler-edit')
                    ->color('warning')
                    ->visible(fn (Client $record): bool => $record->absence !== null)
                    ->modal()
                    ->fillForm(fn (Client $record): array => $record->absence?->toArray() ?? [])
                    ->schema(fn (Schema $schema) => AbsenceForm::configure($schema))
                    ->action(function (array $data, Client $record): void {
                        $record->absence?->update($data);
                    }),
                Action::make('deleteAbsence')
                    ->label('Supprimer l\'absence')
                    ->icon('tabler-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Client $record): bool => $record->absence !== null)
                    ->action(function (Client $record): void {
                        $record->absence?->delete();
                    }),

            ])
                ->label('Ajouter...')
                ->color('success')
                ->icon('tabler-plus')
                ->dropdownWidth(Width::FitContent)
                ->button(),

            DeleteAction::make()
                ->label('Supprimer le client')
                ->icon('tabler-trash'),
        ];
    }
}
