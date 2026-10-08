<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Schemas;

use AcMarche\MailingList\Filament\Actions\TestSmtpAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SenderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Section::make('Pied de page')
                        ->schema([
                            TextEntry::make('footer')
                                ->label('Pied de page')
                                ->hiddenLabel()
                                ->html()
                                ->prose()
                                ->placeholder('Aucun pied de page')
                                ->columnSpanFull(),
                        ])
                        ->grow(),
                    Section::make('Logo')
                        ->schema([
                            ImageEntry::make('logo')
                                ->label('Logo')
                                ->disk('public')
                                ->placeholder('Aucun logo')
                                ->hiddenLabel(),
                            TextEntry::make('created_at')
                                ->label('Créé le')
                                ->dateTime(),
                            TextEntry::make('updated_at')
                                ->label('Modifié le')
                                ->dateTime(),
                        ])
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
                Section::make('Configuration SMTP')
                    ->key('smtpConfiguration')
                    ->afterHeader([
                        TestSmtpAction::make(),
                    ])
                    ->schema([
                        Flex::make([
                            TextEntry::make('smtp_host')
                                ->label('Hôte SMTP')
                                ->placeholder('Configuration globale'),
                            TextEntry::make('smtp_port')
                                ->label('Port SMTP')
                                ->placeholder('—'),
                            TextEntry::make('smtp_username')
                                ->label('Identifiant SMTP')
                                ->placeholder('—'),
                            IconEntry::make('smtp_password')
                                ->label('Mot de passe SMTP')
                                ->state(fn($record): bool => filled($record->smtp_password))
                                ->boolean()
                                ->trueIcon('heroicon-o-lock-closed')
                                ->falseIcon('heroicon-o-lock-open')
                                ->trueColor('success')
                                ->falseColor('gray'),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
