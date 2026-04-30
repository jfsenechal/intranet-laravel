<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Schemas;

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Filament\Actions\SendAction;
use AcMarche\MailingList\Models\Email;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Callout::make('Les pièces jointes dépassent la limite de '.SendAction::MAX_ATTACHMENTS_SIZE_MB.' Mo')
                    ->description(fn (Email $record): string => 'Taille actuelle : '.SendAction::attachmentsSizeMb($record).' Mo. Réduisez la taille des pièces jointes avant d\'envoyer.')
                    ->danger()
                    ->visible(fn (Email $record): bool => SendAction::attachmentsSizeExceeded($record)),
                Callout::make('Aucun destinataire')
                    ->description('Ajoutez au moins un destinataire avant d\'envoyer.')
                    ->warning()
                    ->visible(fn (Email $record): bool => $record->total_count < 1),
                Flex::make([
                    Section::make('Informations')
                        ->label(null)
                        ->schema([
                            Flex::make([
                                TextEntry::make('sender.name')
                                    ->label('Nom de l\'expéditeur'),
                                TextEntry::make('sender.email')
                                    ->label('Expéditeur Email'),
                            ]),
                            TextEntry::make('body')
                                ->label('Contenu')
                                ->html()
                                ->prose()
                                ->columnSpanFull(),
                            RepeatableEntry::make('recipients')
                                ->label('Destinataires')
                                ->schema([
                                    TextEntry::make('name'),
                                    TextEntry::make('email_address'),
                                    TextEntry::make('status')
                                        ->badge()
                                        ->formatStateUsing(fn (RecipientStatus $state): string => $state->getLabel()),
                                ])
                                ->columns(3)
                                ->columnSpanFull(),
                        ])
                        ->grow(),
                    Section::make('Statut')
                        ->label(null)
                        ->schema([
                            TextEntry::make('status')
                                ->label('Etat de l\'envoi')
                                ->badge()
                                ->color(fn (EmailStatus $state): string => match ($state) {
                                    EmailStatus::Draft => 'gray',
                                    EmailStatus::Sending => 'warning',
                                    EmailStatus::Sent => 'success',
                                    EmailStatus::Failed => 'danger',
                                }),
                            TextEntry::make('total_count')
                                ->label('Destinataires'),
                        ])
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
            ]);
    }
}
