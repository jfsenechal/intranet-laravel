<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewCategorie extends ViewRecord
{
    #[Override]
    protected static string $resource = CategorieResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('parent.name')
                            ->label('Catégorie parente')
                            ->placeholder('—'),
                        TextEntry::make('slug')->label('Slug'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        IconEntry::make('icon')
                            ->label('Icône')
                            ->icon(fn (?string $state): ?string => $state),
                        ColorEntry::make('color')->label('Couleur'),
                        TextEntry::make('departments')
                            ->label('Départements')
                            ->badge()
                            ->separator(','),
                        IconEntry::make('public')
                            ->label('Public')
                            ->boolean(),
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
