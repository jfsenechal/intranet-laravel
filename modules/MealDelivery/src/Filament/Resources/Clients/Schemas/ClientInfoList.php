<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Schemas;

use AcMarche\MealDelivery\Filament\Resources\Notes\NoteResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Note;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Adresse')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('street')
                                                    ->label('Rue')
                                                    ->columnSpan(2),

                                                TextEntry::make('number')
                                                    ->label('Numéro'),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('postal_code')
                                                    ->label('Code postal'),

                                                TextEntry::make('city')
                                                    ->label('Localité')
                                                    ->columnSpan(2),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('birth_date')
                                                    ->label('Né le')
                                                    ->date()
                                                    ->placeholder('—'),

                                                TextEntry::make('floor')
                                                    ->label('Etage')
                                                    ->placeholder('—'),
                                            ]),
                                    ]),

                                Section::make('Contact')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('phone')
                                            ->label('Téléphone')
                                            ->helperText('Du résidant')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('contact_name')
                                            ->label('Personne de contact')
                                            ->placeholder('—'),

                                        TextEntry::make('contact_phone')
                                            ->label('Téléphone du contact')
                                            ->placeholder('—')
                                            ->copyable(),

                                        TextEntry::make('contact_notes')
                                            ->label('Contact remarque')
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Remarques')
                                    ->schema([
                                        TextEntry::make('notes')
                                            ->label('Remarques')
                                            ->hiddenLabel()
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Notes')
                                    ->schema([
                                        RepeatableEntry::make('notes_list')
                                            ->hiddenLabel()
                                            ->state(fn (Client $record): array => self::buildNotes($record))
                                            ->placeholder('Aucune note')
                                            ->table([
                                                TableColumn::make('Date'),
                                                TableColumn::make('Description'),
                                                TableColumn::make('Traitée'),
                                                TableColumn::make('Ajoutée par'),
                                            ])
                                            ->schema([
                                                TextEntry::make('note_date')
                                                    ->url(function (TextEntry $component): ?string {
                                                        $row = $component->getContainer()->getConstantState();

                                                        return is_array($row) ? ($row['note_url'] ?? null) : null;
                                                    }),

                                                TextEntry::make('description'),

                                                IconEntry::make('is_done')
                                                    ->boolean(),

                                                TextEntry::make('user_add')
                                                    ->placeholder('—'),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Absence')
                                    ->columns(2)
                                    ->visible(fn ($record): bool => $record->absence !== null)
                                    ->schema([
                                        TextEntry::make('absence.start_date')
                                            ->label('Du')
                                            ->date()
                                            ->placeholder('—'),

                                        TextEntry::make('absence.end_date')
                                            ->label('Au')
                                            ->date()
                                            ->placeholder('—'),
                                    ]),

                            ]),

                        Grid::make(2)
                            ->schema([
                                Section::make('Options')
                                    ->columnSpanFull()
                                    ->columns(1)
                                    ->schema([
                                        IconEntry::make('is_active')
                                            ->label('Actif')
                                            ->helperText('Si la personne ne commande plus, décochez cette case, elle ne sera plus proposée dans les futures commandes et dans les tournées')
                                            ->boolean(),

                                        IconEntry::make('use_cafeteria')
                                            ->label('Mange à la cafétéria')
                                            ->boolean(),
                                    ]),
                                Section::make('Paramètres de la tournée')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('deliveryRoute.name')
                                            ->label('Tournée')
                                            ->placeholder('—'),

                                        TextEntry::make('recurring_order')
                                            ->label('Commande récurrente')
                                            ->placeholder('—'),

                                        TextEntry::make('diets.name')
                                            ->label('Régimes')
                                            ->badge()
                                            ->placeholder('—'),
                                    ]),

                            ]),

                    ]),
            ]);
    }

    /**
     * `notes` is also a text column on the client, so `$record->notes` resolves to
     * that column and the relation has to be queried explicitly.
     *
     * @return array<int, array{note_date: string, note_url: string, description: string, is_done: bool, user_add: ?string}>
     */
    private static function buildNotes(Client $client): array
    {
        return $client->notes()
            ->orderByDesc('note_date')
            ->get()
            ->map(fn (Note $note): array => [
                'note_date' => $note->note_date?->format('d/m/Y') ?? '',
                'note_url' => NoteResource::getUrl('view', ['record' => $note->id]),
                'description' => (string) $note->description,
                'is_done' => (bool) $note->is_done,
                'user_add' => $note->user_add,
            ])
            ->all();
    }
}
