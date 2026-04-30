<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service\Pages;

use AcMarche\Pst\Filament\Resources\Service\RelationManagers\ActionsRelationManager;
use AcMarche\Pst\Filament\Resources\Service\ServiceResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Override;

final class ViewService extends ViewRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Fieldset::make('users_tab')
                ->label('Agents')
                ->schema([
                    TextEntry::make('users')
                        ->hiddenLabel()
                        ->badge()
                        ->formatStateUsing(fn (User $state): string => $state->last_name.' '.$state->first_name),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    protected function getAllRelationManagers(): array
    {
        $relations = $this->getResource()::getRelations();
        $relations[] = ActionsRelationManager::class;

        return $relations;
    }
}
