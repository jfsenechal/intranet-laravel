<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TeleworkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Agent')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user_add')
                            ->label('Utilisateur'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i'),
                    ]),
                Section::make('Adresse de télétravail')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('street')
                            ->label('Rue'),
                        TextEntry::make('postal_code')
                            ->label('Code postal'),
                        TextEntry::make('locality')
                            ->label('Localité'),
                    ]),
                Section::make('Modalités')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('location_type')
                            ->label('Lieu')
                            ->badge(),
                        TextEntry::make('day_type')
                            ->label('Type de jour')
                            ->badge(),
                        TextEntry::make('fixed_day')
                            ->label('Jour fixe')
                            ->badge(),
                        TextEntry::make('variable_day_reason')
                            ->label('Motivation jour variable')
                            ->html()
                            ->columnSpanFull(),
                    ]),
                Section::make('Accords')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('regulation_agreement')
                            ->label('Accord règlement')
                            ->boolean(),
                        IconEntry::make('it_agreement')
                            ->label('Accord informatique')
                            ->boolean(),
                    ]),
                Section::make('Validation par la direction de service')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('manager_validated')
                            ->label('Validé')
                            ->boolean(),
                        TextEntry::make('manager_validated_at')
                            ->label('Date de validation')
                            ->date('d/m/Y'),
                        TextEntry::make('manager_validator_name')
                            ->label('Validé par'),
                        TextEntry::make('manager_validation_notes')
                            ->label('Notes de la direction du service')
                            ->html()
                            ->columnSpanFull(),
                    ]),
                Section::make('GRH')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('date_college')
                            ->label('Date collège')
                            ->date('d/m/Y'),
                        TextEntry::make('hr_validator_name')
                            ->label('Nom GRH'),
                        TextEntry::make('hr_notes')
                            ->label('Notes GRH')
                            ->html()
                            ->columnSpanFull(),
                    ]),
                Section::make('Remarques de l\'agent')
                    ->schema([
                        TextEntry::make('employee_notes')
                            ->label('Remarques')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
