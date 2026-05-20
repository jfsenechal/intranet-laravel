<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PvForm
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
                                ->maxLength(100)
                                ->columnSpan(1),
                            DatePicker::make('meeting_date')
                                ->label('Date du procès-verbal')
                                ->required()
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file_name')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory('conseil/minutes')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
