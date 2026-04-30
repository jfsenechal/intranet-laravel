<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SmsReminderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make('Numéro')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('Numéro')
                            ->tel()
                            ->helperText('Format: 32476642612'),
                    ]),
                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel')
                            ->required(),
                        DatePicker::make('other_reminder_date')
                            ->label('Autre date de rappel'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        Textarea::make('message')
                            ->label('Message')
                            ->helperText('Max 160 caractères. caractères')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function forSending(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Données')
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('Numéro')
                            ->tel()
                            ->helperText('Format: 32476642612'),
                        Textarea::make('message')
                            ->label('Message')
                            ->helperText('Max 160 caractères. caractères')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
