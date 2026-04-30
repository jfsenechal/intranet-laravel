<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Schemas;

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Enums\WeekdayEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

final class TeleworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Adresse de télétravail')
                    ->columns(3)
                    ->schema([
                        TextInput::make('street')
                            ->label('Rue')
                            ->maxLength(120),
                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->maxLength(10),
                        TextInput::make('locality')
                            ->label('Localité')
                            ->maxLength(120),
                    ]),
                Section::make('Modalités')
                    ->columns(2)
                    ->schema([
                        Select::make('location_type')
                            ->label('Lieu')
                            ->options(LocationTypeEnum::class)
                            ->enum(LocationTypeEnum::class)
                            ->required(),
                        Select::make('day_type')
                            ->label('Type de jour')
                            ->options(DayTypeEnum::class)
                            ->enum(DayTypeEnum::class)
                            ->required()
                            ->live(),
                        Select::make('fixed_day')
                            ->label('Jour fixe')
                            ->options(WeekdayEnum::class)
                            ->enum(WeekdayEnum::class)
                            ->visible(fn (Get $get): bool => self::dayType($get('day_type')) === DayTypeEnum::Fixe),
                        RichEditor::make('variable_day_reason')
                            ->label('Motivation jour variable')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => self::dayType($get('day_type')) === DayTypeEnum::Variable),
                        RichEditor::make('employee_notes')
                            ->label('Remarques')
                            ->helperText('Avez vous une remarque particulière?')
                            ->columnSpanFull(),
                    ]),
                Section::make('Accords')
                    ->columns(2)
                    ->schema([
                        Toggle::make('regulation_agreement')
                            ->label('J\'ai lu et accepte le règlement')
                            ->helperText(
                                new HtmlString(
                                    '<a href="'.DocumentResource::getUrl(
                                        'view',
                                        ['record' => 13],
                                        panel: 'document-panel'
                                    ).'" target="_blank" class="text-primary underline">Consulter le règlement</a>'
                                )
                            )
                            ->required(),
                        Toggle::make('it_agreement')
                            ->label(
                                'Je déclare m’engager à respecter les règles de sécurité informatique imposées par l’employeur.'
                            )
                            ->required(),
                    ]),
            ]);
    }

    public static function validationService(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Validation du directeur')
                    ->columns(2)
                    ->schema([
                        Toggle::make('manager_validated')
                            ->label('Validé'),
                        DatePicker::make('manager_validated_at')
                            ->label('Date de validation'),
                        TextInput::make('manager_validator_name')
                            ->label('Nom du directeur')
                            ->maxLength(100),
                        RichEditor::make('manager_validation_notes')
                            ->label('Notes du directeur')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function validationGrh(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations du GRH')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('date_college')
                            ->label('Date collège'),
                        TextInput::make('hr_validator_name')
                            ->label('Nom GRH')
                            ->maxLength(100),
                        RichEditor::make('hr_notes')
                            ->label('Notes GRH')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function dayType(mixed $value): ?DayTypeEnum
    {
        if ($value instanceof DayTypeEnum) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return DayTypeEnum::tryFrom((int) $value);
    }
}
