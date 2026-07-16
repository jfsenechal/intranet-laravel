<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmployeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextInput::make('givenName')
                            ->label('Prénom')
                            ->maxLength(64),
                        TextInput::make('sn')
                            ->label('Nom')
                            ->required()
                            ->maxLength(64),
                        TextInput::make('initials')
                            ->label('Initiales')
                            ->maxLength(6),
                    ]),
                Section::make('Fonction')
                    ->columns()
                    ->components([
                        TextInput::make('title')
                            ->label('Fonction')
                            ->maxLength(128),
                        TextInput::make('department')
                            ->label('Service')
                            ->maxLength(64),
                        TextInput::make('company')
                            ->label('Société')
                            ->maxLength(64)
                            ->helperText('AC Marche, Cpas, Maison du tourisme')
                            ->columnSpanFull(),
                    ]),
                self::contact(),
                Section::make('Adresse')
                    ->columns()
                    ->components([
                        TextInput::make('streetAddress')
                            ->label('Rue')
                            ->maxLength(128)
                            ->columnSpanFull(),
                        TextInput::make('postalCode')
                            ->label('Code postal')
                            ->maxLength(40),
                        TextInput::make('l')
                            ->label('Localité')
                            ->maxLength(128),
                        TextInput::make('co')
                            ->label('Pays')
                            ->maxLength(128),
                    ]),
                Section::make('Divers')
                    ->components([
                        TextInput::make('wWWHomePage')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->maxLength(1000)
                            ->helperText("Peut être lu par l'utilisateur")
                            ->columnSpanFull(),
                        Textarea::make('info')
                            ->label('Notes')
                            ->rows(2)
                            ->maxLength(1000)
                            ->helperText("Peut être lu par l'utilisateur")
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, Section>
     */
    public static function forEmail(): array
    {
        return [
            Section::make('Adresse')
                ->columns()
                ->components([
                    TextInput::make('mail')
                        ->label('Adresse mail')
                        ->required()
                        ->maxLength(200)
                        ->rule('regex:/^[A-Za-z0-9._%+-]+$/')
                        ->validationMessages([
                            'regex' => "L'adresse ne peut pas contenir de @ : le domaine se choisit à côté.",
                        ])
                        ->helperText('Sans le domaine, par exemple prenom.nom'),
                    Select::make('extension')
                        ->label('Domaine')
                        ->options(EmailExtensionEnum::class)
                        ->default(EmailExtensionEnum::EXTENSION_AC)
                        ->selectablePlaceholder(false)
                        ->required(),
                    Checkbox::make('force')
                        ->label('Forcer')
                        ->helperText(
                            "Enregistrer même si l'adresse est déjà utilisée par une autre fiche de l'annuaire."
                        )
                        ->columnSpanFull(),
                ]),
        ];
    }

    private static function contact(): Section
    {
        return Section::make('Coordonnées')
            ->columns()
            ->components([
                TextInput::make('telephoneNumber')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(64),
                TextInput::make('ipPhone')
                    ->label('Extension téléphone')
                    ->maxLength(64),
                TextInput::make('mobile')
                    ->label('GSM')
                    ->tel()
                    ->maxLength(64),
            ]);
    }
}
