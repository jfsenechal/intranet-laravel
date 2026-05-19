<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groups\Pages;

use AcMarche\Conseil\Filament\Resources\Groups\GroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewGroup extends ViewRecord
{
    #[Override]
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('recipients_count')
                            ->label('Destinataires')
                            ->state(fn ($record): int => $record->recipients()->count()),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
