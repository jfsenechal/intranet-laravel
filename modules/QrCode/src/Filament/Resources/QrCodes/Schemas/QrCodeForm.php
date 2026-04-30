<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Schemas;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class QrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(150),
                        Select::make('action')
                            ->label('Action')
                            ->options(QrCodeActionEnum::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Select $component) => $component
                                ->getContainer()
                                ->getComponent('dynamicTypeFields')
                                ?->getChildSchema()
                                ?->fill()),
                    ]),

                Grid::make(2)
                    ->schema(fn (Get $get): array => self::fieldsForType($get('action')))
                    ->key('dynamicTypeFields'),

                Section::make('Apparence')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        ColorPicker::make('color')
                            ->label('Couleur')
                            ->default('#000000'),
                        ColorPicker::make('background_color')
                            ->label('Fond')
                            ->default('#FFFFFF'),
                        Select::make('format')
                            ->label('Format')
                            ->options([
                                'SVG' => 'SVG',
                                'PNG' => 'PNG',
                                'EPS' => 'EPS',
                            ])
                            ->default('SVG')
                            ->required(),
                        Select::make('style')
                            ->label('Style')
                            ->options([
                                'square' => 'Carré',
                                'dot' => 'Points',
                                'round' => 'Arrondi',
                            ])
                            ->default('square')
                            ->required(),
                        TextInput::make('pixels')
                            ->label('Taille (px)')
                            ->numeric()
                            ->minValue(50)
                            ->maxValue(2000)
                            ->default(400),
                        TextInput::make('margin')
                            ->label('Marge')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->default(10),
                    ]),
            ]);
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component|\Filament\Forms\Components\Field>
     */
    public static function fieldsForType(QrCodeActionEnum|string|null $action): array
    {
        $action = $action instanceof QrCodeActionEnum ? $action : QrCodeActionEnum::tryFrom((string) $action);

        return match ($action) {
            QrCodeActionEnum::URL => [
                Section::make('URL')->schema([
                    TextInput::make('message')
                        ->label('URL')
                        ->url()
                        ->required()
                        ->placeholder('https://example.com')
                        ->maxLength(500),
                ]),
            ],

            QrCodeActionEnum::TEXT => [
                Section::make('Texte')->schema([
                    Textarea::make('message')
                        ->label('Texte')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),
                ]),
            ],

            QrCodeActionEnum::PHONE_NUMBER => [
                Section::make('Téléphone')->schema([
                    TextInput::make('phone_number')
                        ->label('Numéro de téléphone')
                        ->tel()
                        ->required()
                        ->maxLength(50),
                ]),
            ],

            QrCodeActionEnum::SMS => [
                Section::make('SMS')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('Destinataire')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ],

            QrCodeActionEnum::EMAIL => [
                Section::make('Email')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Destinataire')
                            ->email()
                            ->required()
                            ->maxLength(150),
                        TextInput::make('subject')
                            ->label('Sujet')
                            ->maxLength(150),
                        Textarea::make('message')
                            ->label('Corps du message')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ],

            QrCodeActionEnum::WIFI => [
                Section::make('Wifi')
                    ->columns(2)
                    ->schema([
                        TextInput::make('ssid')
                            ->label('SSID')
                            ->helperText('Nom du réseaux')
                            ->required()
                            ->maxLength(100),
                        Select::make('encryption')
                            ->label('Chiffrement')
                            ->options([
                                'WPA' => 'WPA / WPA2',
                                'WEP' => 'WEP',
                                'nopass' => 'Aucun',
                            ])
                            ->default('WPA')
                            ->required(),
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->maxLength(150),
                        Toggle::make('network_hidden')
                            ->label('Réseau caché')
                            ->default(false),
                    ]),
            ],

            QrCodeActionEnum::GEO => [
                Section::make('Coordonnées GPS')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->required()
                            ->maxLength(50),
                    ]),
            ],

            QrCodeActionEnum::EPC => [
                Section::make('Virement SEPA (EPC)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('recipient')
                            ->label('Bénéficiaire')
                            ->required()
                            ->maxLength(70),
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->maxLength(34),
                        TextInput::make('amount')
                            ->label('Montant (EUR)')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('€'),
                        TextInput::make('message')
                            ->label('Communication')
                            ->maxLength(140),
                    ]),
            ],

            default => [],
        };
    }
}
