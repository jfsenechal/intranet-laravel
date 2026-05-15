<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrdreJourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nom')
                                ->label('Nom')
                                ->required()
                                ->maxLength(150)
                                ->columnSpan(1),
                            DateTimePicker::make('date_ordre')
                                ->label('Date de l\'ordre du jour')
                                ->required()
                                ->columnSpan(1),
                            DatePicker::make('date_fin_diffusion')
                                ->label('Date de fin de diffusion')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file_name')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory('conseil/ordres-jour')
                            ->visibility('public')
                            ->required(),
                    ]),
            ]);
    }
}
