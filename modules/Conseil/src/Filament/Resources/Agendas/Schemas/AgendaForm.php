<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Schemas;

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
                                ->helperText('Ex: OJ Conseil 13/04/2026 - 19H00')
                                ->required()
                                ->maxLength(150)
                                ->columnSpan(1),
                            DateTimePicker::make('agenda_date')
                                ->label('Date de l\'ordre du jour')
                                ->required()
                                ->columnSpan(1),
                        ]),
                    ]),
                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file_name')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory(config('conseil.uploads.agendas_directory'))
                            ->visibility('public')
                            ->required(),
                    ]),
            ]);
    }
}
