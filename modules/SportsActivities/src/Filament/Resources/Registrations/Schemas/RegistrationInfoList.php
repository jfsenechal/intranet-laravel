<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Schemas;

use AcMarche\SportsActivities\Models\Member;
use AcMarche\SportsActivities\Models\Registration;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class RegistrationInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RepeatableEntry::make('registrations')
                    ->hiddenLabel()
                    ->table([
                        TableColumn::make('Nom'),
                        TableColumn::make('Adresse'),
                        TableColumn::make('Téléphone ou gsm'),
                        TableColumn::make('Email'),
                        TableColumn::make('Né le'),
                    ])
                    ->schema([
                        TextEntry::make('name')
                            ->state(fn (Registration $record): string => self::memberName($record->member)),
                        TextEntry::make('address')
                            ->state(fn (Registration $record): string => self::memberAddress($record->member)),
                        TextEntry::make('phone')
                            ->placeholder('-')
                            ->state(
                                fn (Registration $record): ?string => $record->member?->phone ?: $record->member?->mobile
                            ),
                        TextEntry::make('email')
                            ->placeholder('-')
                            ->state(fn (Registration $record): ?string => $record->member?->email),
                        TextEntry::make('birth_date')
                            ->placeholder('-')
                            ->state(fn (Registration $record): ?string => $record->member?->birth_date?->format('d/m/Y')
                            ),
                    ]),
            ]);
    }

    private static function memberName(?Member $member): string
    {
        if ($member === null) {
            return '';
        }

        return mb_trim($member->last_name.' '.$member->first_name);
    }

    private static function memberAddress(?Member $member): string
    {
        if ($member === null) {
            return '';
        }

        return mb_trim($member->street.', '.mb_trim($member->postal_code.' '.$member->city), ', ');
    }
}
