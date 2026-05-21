<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RecipientInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email')->label('Email')->placeholder('—'),
                    ]),

                Section::make('Convocations')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('pv_service')->label('PV Service')->boolean(),
                        IconEntry::make('ordre_service')->label('Ordre du jour - Service')->boolean(),
                        IconEntry::make('ordre_college')->label('Ordre du jour - Collège')->boolean(),
                        IconEntry::make('pv_college')->label('PV Collège')->boolean(),
                    ]),
            ]);
    }
}
