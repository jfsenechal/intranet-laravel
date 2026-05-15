<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions\Schemas;

use AcMarche\SportsActivities\Models\Groupe;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class InscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inscription')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('sportif_id')
                                ->label('Sportif')
                                ->relationship(
                                    name: 'sportif',
                                    titleAttribute: 'nom',
                                    modifyQueryUsing: fn ($query) => $query->orderBy('nom'),
                                )
                                ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->nom} {$record->prenom}")
                                ->searchable(['nom', 'prenom'])
                                ->preload()
                                ->required(),
                            Select::make('activite_id')
                                ->label('Activité')
                                ->relationship('activite', 'nom')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('groupe_id', null)),
                            Select::make('groupe_id')
                                ->label('Groupe')
                                ->required()
                                ->options(function (Get $get): array {
                                    $activiteId = $get('activite_id');
                                    if (! $activiteId) {
                                        return [];
                                    }

                                    return Groupe::query()
                                        ->where('activite_id', $activiteId)
                                        ->get()
                                        ->mapWithKeys(fn (Groupe $g): array => [
                                            $g->id => "{$g->jour} — {$g->heure} — {$g->lieux}",
                                        ])
                                        ->toArray();
                                })
                                ->searchable(),
                            TextInput::make('prix')->label('Prix')->numeric(),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                            Textarea::make('remarque')->label('Remarque')->rows(3)->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
