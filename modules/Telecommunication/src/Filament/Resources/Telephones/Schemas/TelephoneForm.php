<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class TelephoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('user_name')
                                ->label('Utilisateur')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                    'slug',
                                    Str::slug($state ?? ''),
                                ))
                                ->columnSpan(1),
                            TextInput::make('number')
                                ->label('Numéro')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('slug')
                                ->label('Identifiant')
                                ->required()
                                ->maxLength(70)
                                ->unique(ignoreRecord: true)
                                ->columnSpan(1),
                            Select::make('line_type_id')
                                ->label('Type de ligne')
                                ->relationship('lineType', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            Toggle::make('archived')
                                ->label('Archivé')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Opérateurs')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('mobistar')
                                ->label('Mobistar')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('proximus')
                                ->label('Proximus')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Affectation')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('service')
                                ->label('Service')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('department')
                                ->label('Département')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('location')
                                ->label('Localisation')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('budget_article')
                                ->label('Article budgétaire')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('fixed_cost')
                                ->label('Coût fixe')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Note')
                    ->schema([
                        Textarea::make('note')
                            ->label('Note')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
