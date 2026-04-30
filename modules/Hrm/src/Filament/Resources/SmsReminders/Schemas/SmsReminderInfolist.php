<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas;

use AcMarche\Hrm\Models\SmsReminder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SmsReminderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Section::make('Numéro')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('phone_number')
                                    ->label('Numéro de téléphone'),
                            ]),
                        Fieldset::make('Dates')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('reminder_date')
                                    ->label('Date de rappel')
                                    ->date('d/m/Y'),
                                TextEntry::make('other_reminder_date')
                                    ->label('Autre date de rappel')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                            ]),
                        Section::make('Message')
                            ->schema([
                                TextEntry::make('message')
                                    ->label('Message')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('employee.last_name')
                            ->label('Agent')
                            ->formatStateUsing(
                                fn (
                                    $state,
                                    SmsReminder $record
                                ): string => $record->employee?->last_name.' '.$record->employee?->first_name
                            ),
                        TextEntry::make('sent_at')
                            ->label('Envoyé le')
                            ->date('d/m/Y'),
                        TextEntry::make('result')
                            ->label('Résultat de l\'envoi'),
                        TextEntry::make('updated_by')
                            ->label('Créé par')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
