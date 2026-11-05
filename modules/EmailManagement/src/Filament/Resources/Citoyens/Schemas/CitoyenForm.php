<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use AcMarche\EmailManagement\Service\EmailService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;

/**
 * Only the fillable columns are fields here: the mailbox identity (mail, uid, home
 * directory) and the token hashes are left out of the schema entirely, so a save can never
 * submit them. Only {@see self::forCreating()} asks for the mailbox, which cannot change
 * afterwards.
 */
final class CitoyenForm
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
                            ->maxLength(255),
                        TextInput::make('sn')
                            ->label('Nom')
                            ->maxLength(255),
                    ]),
                Section::make('Messagerie')
                    ->relationship('virtualUser')
                    ->components([
                        Toggle::make('active')
                            ->label('Actif'),
                    ]),
                Section::make('Adresse')
                    ->columns()
                    ->components([
                        TextInput::make('postalAddress')
                            ->label('Rue')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('postalCode')
                            ->label('Code postal')
                            ->maxLength(255),
                        TextInput::make('l')
                            ->label('Localité')
                            ->maxLength(255),
                    ]),
                Section::make('Récupération du compte')
                    ->columns()
                    ->components([
                        TextInput::make('recovery_email')
                            ->label('Email de récupération')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('recovery_phone')
                            ->label('Téléphone de récupération')
                            ->tel()
                            ->maxLength(255),
                    ]),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
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
                            ->maxLength(255),
                        TextInput::make('givenName')
                            ->label('Prénom')
                            ->maxLength(255),
                        TextInput::make('mail')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(64)
                            ->regex('/^[a-z0-9._-]+@marche\.be$/i')
                            ->validationMessages(['regex' => "L'adresse doit se terminer par @marche.be."])
                            ->unique(Citoyen::class, 'mail')
                            ->rule(Rule::unique(VirtualUser::class, 'username'))
                            ->columnSpanFull()
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
                                            EmailService::sanitizeForEmail($givenName).'.'.EmailService::sanitizeForEmail($sn).'@marche.be'
                                        );
                                    }),
                            ),
                    ]),
                Section::make('Coordonnées')
                    ->columns()
                    ->components([
                        TextInput::make('employeeNumber')
                            ->label('Numéro national')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('postalAddress')
                            ->label('Rue et numéro')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('postalCode')
                            ->label('Code postal')
                            ->default('6900')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('l')
                            ->label('Localité')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Compte')
                    ->columns()
                    ->components([
                        PasswordInput::make(),
                        TextInput::make('quota_bytes')
                            ->label('Quota')
                            ->numeric()
                            ->required()
                            ->minValue(150)
                            ->maxValue(4000)
                            ->default(350)
                            ->suffix('Mo'),
                    ]),
                Section::make('Divers')
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
