<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Odd\Pages;

use AcMarche\Pst\Filament\Resources\Odd\OddResource;
use AcMarche\Pst\Filament\Resources\Odd\RelationManagers\ActionsRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewOdd extends ViewRecord
{
    #[Override]
    protected static string $resource = OddResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('descripton')
                    ->hiddenLabel()
                    ->columnSpanFull(),
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
