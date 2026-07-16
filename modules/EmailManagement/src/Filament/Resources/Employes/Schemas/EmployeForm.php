<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use AcMarche\EmailManagement\Filament\Schemas\PasswordInput;
use AcMarche\EmailManagement\Service\EmailService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

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
                        TextInput::make('samaccountname')
                            ->label('Identifiant')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText("L'identifiant ne peut pas être modifié après la création."),
                        TextInput::make('mail')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                self::contact(),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function forCreating(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextInput::make('sn')
                            ->label('Nom')
                            ->required()
                            ->maxLength(64)
                            ->live(onBlur: true),
                        TextInput::make('givenName')
                            ->label('Prénom')
                            ->maxLength(64)
                            ->live(onBlur: true),
                        TextInput::make('samaccountname')
                            ->label('Identifiant')
                            ->required()
                            ->maxLength(20)
                            ->unique('maria-email-management.employes', 'samaccountname')
                            ->helperText('Identifiant de connexion Active Directory.')
                            ->afterContent(
                                Action::make('generateSamAccountName')
                                    ->label('Générer')
                                    ->icon(Heroicon::Sparkles)
                                    ->color('gray')
                                    ->action(function (Get $schemaGet, Set $schemaSet): void {
                                        $givenName = $schemaGet('givenName');
                                        $sn = $schemaGet('sn');

                                        if (blank($givenName) || blank($sn)) {
                                            return;
                                        }

                                        $schemaSet(
                                            'samaccountname',
                                            Str::lower(Str::substr(EmailService::sanitizeForEmail($givenName), 0, 1)
                                                .EmailService::sanitizeForEmail($sn))
                                        );
                                    }),
                            ),
                        TextInput::make('mail')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('maria-email-management.employes', 'mail')
                            ->afterContent(
                                Action::make('generateEmail')
                                    ->label('Générer')
                                    ->icon(Heroicon::Sparkles)
                                    ->color('gray')
                                    ->action(function (Get $schemaGet, Set $schemaSet): void {
                                        $givenName = $schemaGet('givenName');
                                        $sn = $schemaGet('sn');

                                        if (blank($givenName) || blank($sn)) {
                                            return;
                                        }

                                        $schemaSet(
                                            'mail',
                                            EmailService::sanitizeForEmail(
                                                $givenName
                                            ).'.'.EmailService::sanitizeForEmail($sn).'@ac.marche.be'
                                        );
                                    }),
                            ),
                    ]),
                self::contact(),
                Section::make('Compte')
                    ->columns()
                    ->components(
                        PasswordInput::create(),
                    ),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
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
            ]);
    }
}
