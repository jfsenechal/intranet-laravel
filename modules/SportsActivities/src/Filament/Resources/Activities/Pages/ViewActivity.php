<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activities\ActivityResource;
use AcMarche\SportsActivities\Filament\Resources\Groups\Schemas\GroupForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewActivity extends ViewRecord
{
    #[Override]
    protected static string $resource = ActivityResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addGroup')
                ->label('Ajouter un groupe')
                ->icon(Heroicon::UserGroup)
                ->schema(GroupForm::schema())
                ->action(function (array $data): void {
                    $this->record->groups()->create($data);
                }),
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
