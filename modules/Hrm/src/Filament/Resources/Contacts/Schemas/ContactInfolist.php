<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email_1')
                            ->label('Email 1')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('phone_1')
                            ->label('Téléphone 1')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('email_2')
                            ->label('Email 2')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('phone_2')
                            ->label('Téléphone 2')
                            ->icon('heroicon-o-phone'),
                    ]),
                Section::make('Adresse')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('street')
                            ->label('Rue')
                            ->columnSpanFull(),
                        TextEntry::make('postal_code')
                            ->label('Code postal'),
                        TextEntry::make('city')
                            ->label('Ville'),
                    ]),
                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
