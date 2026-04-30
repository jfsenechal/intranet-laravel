<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Pages;

use AcMarche\Pst\Actions\CanPaginateViewRecordTrait;
use AcMarche\Pst\Actions\ReminderAction;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Filament\Resources\ActionPst\Schemas\ActionInfolist;
use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use AcMarche\Pst\Models\Action as ActionModel;
use Filament\Actions\Action as ActionAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Override;

final class ViewActionPst extends ViewRecord
{
    use CanPaginateViewRecordTrait;

    #[Override]
    protected static string $resource = ActionPstResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    public function getBreadcrumbs(): array
    {
        $oo = $this->record->operationalObjective()->first();
        $os = $oo->strategicObjective()->first();

        return [
            StrategicObjectiveResource::getUrl('index') => 'Objectifs Stratégiques',
            StrategicObjectiveResource::getUrl('view', ['record' => $os]) => $os->name,
            OperationalObjectiveResource::getUrl('view', ['record' => $oo]) => $oo->name,
            'Action',
            // $this->getBreadcrumb(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return ActionInfolist::infolist($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            //  PreviousAction::make(),
            //  NextAction::make(),
            ActionGroup::make([
                ActionAction::make('rapport')
                    ->label('Export en pdf')
                    ->icon('tabler-pdf')
                    ->url(fn (ActionModel $record): string => route('export.action', $record))
                    ->action(function (): void {
                        Notification::make()
                            ->title('Pdf exporté')
                            ->success()
                            ->send();
                    }),
                ReminderAction::createAction($this->record),
                DeleteAction::make()
                    ->label('Supprimer l\'action')
                    ->icon('tabler-trash'),
            ]
            )
                ->label('Autres actions')
                ->button()
                ->size(Size::Large)
                ->color('secondary'),
        ];
    }
}
