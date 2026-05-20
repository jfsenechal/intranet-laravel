<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Schemas;

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('civilite')
                                ->label('Civilité')
                                ->options(CiviliteEnum::class)
                                ->native(false),
                            Toggle::make('enabled')
                                ->label('Actif')
                                ->default(true),
                            TextInput::make('nom')
                                ->label('Nom')
                                ->required()
                                ->maxLength(50),
                            TextInput::make('prenom')
                                ->label('Prénom')
                                ->required()
                                ->maxLength(50),
                            DatePicker::make('inscrit_le')
                                ->label('Inscrit le')
                                ->displayFormat('d/m/Y')
                                ->native(false),
                        ]),
                    ]),

                Section::make('Adresse')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('rue')
                                ->label('Rue')
                                ->maxLength(150)
                                ->columnSpan(2),
                            TextInput::make('numero')
                                ->label('N°')
                                ->maxLength(50),
                            TextInput::make('codepostal')
                                ->label('Code postal')
                                ->numeric(),
                            TextInput::make('localite')
                                ->label('Localité')
                                ->maxLength(50)
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Contact')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('gsm')
                                ->label('GSM')
                                ->tel()
                                ->maxLength(50),
                            TextInput::make('telephone')
                                ->label('Téléphone')
                                ->tel()
                                ->maxLength(50),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(50),
                        ]),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('remarque')
                            ->label('Remarque')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
