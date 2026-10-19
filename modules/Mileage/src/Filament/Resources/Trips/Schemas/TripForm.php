<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Schemas;

use AcMarche\Mileage\Models\Trip;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

final class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Callout::make('Déplacement lié à une déclaration')
                    ->icon(Heroicon::LockClosed)
                    ->warning()
                    ->description('Ce déplacement est déjà lié à une déclaration : seul un administrateur peut encore le modifier ou le supprimer.')
                    ->visible(fn (?Trip $record): bool => $record?->isDeclared() ?? false)
                    ->columnSpanFull(),
                Section::make('Informations du déplacement')
                    ->schema([
                        TextInput::make('distance')
                            ->label('Distance (km)(Aller,Retour)')
                            ->required()
                            ->numeric()
                            ->suffix('km'),
                        DateTimePicker::make('departure_date')
                            ->label('Date du déplacement')
                            ->helperText('')
                            ->date()
                            ->time(false)
                            ->seconds(false)
                            // Only the day is encoded: store it at 08h00 rather than midnight.
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                                ? Carbon::parse($state)->setTime(8, 0)->format('Y-m-d H:i:s')
                                : null)
                            // A trip date is a Belgian wall clock, not an instant: it is stored
                            // and read back verbatim, so the picker must not convert it.
                            ->timezone(config('app.timezone'))
                            ->required(),
                        Textarea::make('content')
                            ->label('Détail des courses')
                            ->helperText("Lieu de départ + lieu d'arrivée et Objet (Formation, suivi de chantier, autre lieu de prestation)")
                            ->maxLength(150)
                            ->placeholder("Lieu de départ: \nLieu d'arrivée: \nObjet: ")
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Autres frais')
                    ->schema([
                        TextInput::make('meal_expense')
                            ->label('Frais de repas')
                            ->helperText('Max 12,30 euros')
                            ->numeric()
                            ->maxValue(12.3)
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('train_expense')
                            ->label('Frais de train ou de parking')
                            ->helperText('<!> Souche')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
                    ]),
            ]);
    }
}
