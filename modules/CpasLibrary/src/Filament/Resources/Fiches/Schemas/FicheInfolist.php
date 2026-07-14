<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Schemas;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class FicheInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Contenu')
                            ->columns(1)
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description')
                                    ->html()
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                            ]),

                        Section::make('Rappel')
                            ->visible(fn (Fiche $record): bool => $record->date_rappel !== null)
                            ->schema([
                                TextEntry::make('date_rappel')
                                    ->label('Date de rappel')
                                    ->date(),
                            ]),

                        Section::make('Absence')
                            ->columns(2)
                            ->visible(fn (Fiche $record): bool => $record->type === FicheTypeEnum::ABSENCE->value)
                            ->schema([
                                TextEntry::make('date_begin')->label('Date de début')->date(),
                                TextEntry::make('date_end')->label('Date de fin')->date(),
                            ]),

                        Section::make('Législation')
                            ->columns(2)
                            ->visible(fn (Fiche $record): bool => $record->type === FicheTypeEnum::LEGISLATION->value)
                            ->schema([
                                TextEntry::make('type_document')
                                    ->label('Type de document')
                                    ->placeholder('—'),
                                TextEntry::make('source')
                                    ->label('Source')
                                    ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                                    ->placeholder('—'),
                                TextEntry::make('date_promulgation')->label('Promulgation')->date(),
                                TextEntry::make('date_publication')->label('Publication')->date(),
                            ]),

                        Section::make('Suivi')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('userAdd')->label('Auteur')->placeholder('—'),
                                TextEntry::make('createdAt')->label('Créé le')->dateTime(),
                                TextEntry::make('updatedAt')->label('Modifié le')->dateTime(),
                            ]),
                    ]),

                Section::make('Identification')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Catégorie')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => FicheTypeEnum::tryFrom((string) $state)?->getLabel() ?? (string) $state),
                        TextEntry::make('tags.name')
                            ->label('Tags')
                            ->badge()
                            ->separator(',')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
