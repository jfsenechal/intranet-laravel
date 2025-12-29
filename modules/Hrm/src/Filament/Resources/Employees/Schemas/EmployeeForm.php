<?php

namespace AcMarche\Hrm\Filament\Resources\Employees\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

final class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Employee')
                    ->tabs([
                        Tabs\Tab::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Identite')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('civility')
                                            ->label('Civilite')
                                            ->options([
                                                'M.' => 'Monsieur',
                                                'Mme' => 'Madame',
                                                'Mlle' => 'Mademoiselle',
                                            ]),
                                        Forms\Components\TextInput::make('last_name')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('first_name')
                                            ->label('Prenom')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\DatePicker::make('birth_date')
                                            ->label('Date de naissance'),
                                        Forms\Components\Toggle::make('show_birthday')
                                            ->label('Afficher anniversaire')
                                            ->default(true),
                                        Forms\Components\TextInput::make('national_registry_number')
                                            ->label('Registre national')
                                            ->maxLength(100),
                                    ]),
                                Section::make('Coordonnees')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('address')
                                            ->label('Adresse')
                                            ->maxLength(150)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('postal_code')
                                            ->label('Code postal')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('city')
                                            ->label('Ville')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email prive')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('professional_email')
                                            ->label('Email professionnel')
                                            ->email()
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('private_phone')
                                            ->label('Telephone prive')
                                            ->tel()
                                            ->maxLength(150),
                                        Forms\Components\TextInput::make('private_mobile')
                                            ->label('GSM prive')
                                            ->tel()
                                            ->maxLength(150),
                                    ]),
                            ]),
                        Tabs\Tab::make('Emploi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Fieldset::make('Situation')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('job_title')
                                            ->label('Fonction')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('status')
                                            ->label('Statut')
                                            ->options([
                                                'active' => 'Actif',
                                                'retired' => 'Pension',
                                                'terminated' => 'Sorti',
                                                'suspended' => 'Suspendu',
                                            ]),
                                        Forms\Components\TextInput::make('uid')
                                            ->label('Login')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('username')
                                            ->label('Nom utilisateur')
                                            ->maxLength(100),
                                    ]),
                                Fieldset::make('Dates')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\DatePicker::make('hired_at')
                                            ->label('Date entree'),
                                        Forms\Components\DatePicker::make('left_at')
                                            ->label('Date sortie'),
                                        Forms\Components\DatePicker::make('reminder_date')
                                            ->label('Date de rappel'),
                                        Forms\Components\DatePicker::make('salary_seniority_date')
                                            ->label('Anciennete pecuniaire'),
                                        Forms\Components\DatePicker::make('scale_seniority_date')
                                            ->label('Anciennete echelle'),
                                    ]),
                                Fieldset::make('Bareme')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('pay_scale_id')
                                            ->label('Echelle')
                                            ->relationship('payScale', 'title')
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\TextInput::make('pay_scale_code')
                                            ->label('Code bareme')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('allowance')
                                            ->label('Indemnite')
                                            ->maxLength(200),
                                        Forms\Components\TextInput::make('local_unit')
                                            ->label('Unite locale')
                                            ->maxLength(100),
                                    ]),
                            ]),
                        Tabs\Tab::make('Sante')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Forms\Components\Select::make('health_insurance_id')
                                    ->label('Mutuelle')
                                    ->relationship('healthInsurance', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('insurance_affiliation')
                                    ->label('Affiliation mutuelle')
                                    ->maxLength(100),
                            ]),
                        Tabs\Tab::make('Notes')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\RichEditor::make('notes')
                                    ->label('Remarques')
                                    ->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('Photo')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->label('Photo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('uploads/hrm/photos')
                                    ->imageEditor(),
                            ]),
                        Tabs\Tab::make('Parametres')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Toggle::make('is_archived')
                                    ->label('Archive'),
                            ]),
                    ]),
            ]);
    }
}
