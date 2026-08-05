<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas;

use AcMarche\Hrm\Models\SmsReminder;
use Carbon\CarbonInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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
                Hidden::make('employee_id'),
                Fieldset::make('Numéro')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('Numéro')
                            ->tel()
                            ->required()
                            ->helperText('Format: 32476642612'),
                    ]),
                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel')
                            ->minDate(fn (?SmsReminder $record): CarbonInterface => self::earliestSelectableDate($record?->reminder_date))
                            ->validationMessages(['after_or_equal' => 'La date de rappel ne peut pas être antérieure à aujourd\'hui.'])
                            ->required(),
                        DatePicker::make('other_reminder_date')
                            ->label('Autre date de rappel')
                            ->minDate(fn (?SmsReminder $record): CarbonInterface => self::earliestSelectableDate($record?->other_reminder_date))
                            ->validationMessages(['after_or_equal' => 'L\'autre date de rappel ne peut pas être antérieure à aujourd\'hui.']),
                    ]),
                Section::make('Notes')
                    ->schema([
                        Textarea::make('message')
                            ->label('Message')
                            ->helperText('Max 160 caractères. caractères')
                            ->hiddenLabel()
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(160),
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

    /**
     * Blocks past dates, while letting an existing reminder keep a date that is already behind us.
     */
    private static function earliestSelectableDate(?CarbonInterface $currentDate): CarbonInterface
    {
        if ($currentDate !== null && $currentDate->isBefore(today())) {
            return $currentDate;
        }

        return today();
    }
}
