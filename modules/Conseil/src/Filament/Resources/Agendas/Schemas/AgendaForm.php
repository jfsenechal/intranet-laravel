<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AgendaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->maxLength(150)
                                ->columnSpan(1),
                            DateTimePicker::make('agenda_date')
                                ->label('Date de l\'ordre du jour')
                                ->required()
                                ->columnSpan(1),
                            DatePicker::make('distribution_end_date')
                                ->label('Date de fin de diffusion')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file_name')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory('conseil/agendas')
                            ->visibility('public')
                            ->required(),
                    ]),
            ]);
    }
}
