<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Schemas;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Actions\RequestProfileAction;
use AcMarche\Hrm\Filament\Actions\RequestProfileChangeAction;
use AcMarche\Hrm\Filament\Actions\RequestProfileDeletionAction;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

final class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Employee')
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        ImageEntry::make('photo')
                                            ->label('Photo')
                                            ->disk('public')
                                            ->imageHeight(260)
                                            ->defaultImageUrl(
                                                fn (Employee $record
                                                ): string => 'https://ui-avatars.com/api/?size=256&name='.urlencode(
                                                    mb_trim($record->first_name.' '.$record->last_name)
                                                )
                                            )
                                            ->columnSpan(2),
                                        Fieldset::make('Coordonnées')
                                            ->columns(2)
                                            ->columnSpan(10)
                                            ->schema([
                                                TextEntry::make('address')
                                                    ->label('Adresse')
                                                    ->state(
                                                        fn (Employee $record): string => mb_trim(
                                                            $record->address.' '.$record->postal_code.' '.$record->city
                                                        )
                                                    )
                                                    ->columnSpanFull(),
                                                Fieldset::make('Privé')
                                                    ->columns(1)
                                                    ->schema([
                                                        TextEntry::make('private_email')
                                                            ->label('Email')
                                                            ->icon('heroicon-o-envelope'),
                                                        TextEntry::make('private_phone')
                                                            ->label('Téléphone')
                                                            ->icon('heroicon-o-phone'),
                                                        TextEntry::make('private_mobile')
                                                            ->label('GSM')
                                                            ->icon('heroicon-o-device-phone-mobile'),
                                                    ]),
                                                Fieldset::make('Professionnel')
                                                    ->columns(1)
                                                    ->schema([
                                                        TextEntry::make('professional_email')
                                                            ->label('Email')
                                                            ->icon('heroicon-o-envelope'),
                                                        TextEntry::make('professional_phone')
                                                            ->label('Téléphone')
                                                            ->icon('heroicon-o-phone')
                                                            ->state(
                                                                fn (Employee $record
                                                                ): ?string => $record->professional_phone === null ? null : mb_trim(
                                                                    $record->professional_phone.($record->professional_phone_extension !== null ? ' (ext. '.$record->professional_phone_extension.')' : '')
                                                                )
                                                            ),
                                                        TextEntry::make('professional_mobile')
                                                            ->label('GSM')
                                                            ->icon('heroicon-o-device-phone-mobile'),
                                                    ]),
                                            ]),
                                    ]),
                                Section::make('Identité')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('civility')
                                            ->label('Civilité'),
                                        TextEntry::make('birth_date')
                                            ->label('Date de naissance')
                                            ->date('d/m/Y'),
                                        IconEntry::make('show_birthday')
                                            ->label('Afficher la date d\' anniversaire')
                                            ->boolean(),
                                        TextEntry::make('national_registry_number')
                                            ->label('Registre national'),
                                    ]),
                            ]),
                        Tab::make('Emploi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Fieldset::make('Situation')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('job_title')
                                            ->label('Fonction'),
                                        TextEntry::make('status')
                                            ->label('Statut')
                                            ->badge()
                                            ->color(fn (?StatusEnum $state): string => match ($state) {
                                                StatusEnum::AGENT => 'success',
                                                StatusEnum::RETIRED => 'info',
                                                StatusEnum::TERMINATED, StatusEnum::RESIGNED, StatusEnum::ENDED, StatusEnum::CONTRACT_ENDED => 'danger',
                                                StatusEnum::APPLICATION, StatusEnum::INTERN, StatusEnum::STUDENT => 'warning',
                                                default => 'gray',
                                            }),
                                        IconEntry::make('is_archived')
                                            ->label('Archivé')
                                            ->boolean(),
                                        IconEntry::make('is_new_hire')
                                            ->label('Nouvel agent')
                                            ->boolean(),
                                    ]),
                                Fieldset::make('Dates')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('hired_at')
                                            ->label('Date entree')
                                            ->date('d/m/Y'),
                                        TextEntry::make('left_at')
                                            ->label('Date sortie')
                                            ->date('d/m/Y'),
                                        TextEntry::make('reminder_date')
                                            ->label('Date de rappel')
                                            ->date('d/m/Y'),
                                        TextEntry::make('salary_seniority_date')
                                            ->label('Ancienneté pécuniaire')
                                            ->date('d/m/Y'),
                                        TextEntry::make('scale_seniority_date')
                                            ->label('Ancienneté d\'échelle')
                                            ->date('d/m/Y'),
                                    ]),
                                Fieldset::make('Barème')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('payScale.name')
                                            ->label('Echelle'),
                                        TextEntry::make('pay_scale_code')
                                            ->label('Code barème'),
                                        TextEntry::make('prerequisite.name')
                                            ->label('Prérequis')
                                            ->suffixAction(
                                                Action::make('viewPrerequisite')
                                                    ->label('Voir')
                                                    ->icon(Heroicon::OutlinedEye)
                                                    ->modalHeading('Prérequis')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Fermer')
                                                    ->visible(fn (Employee $record): bool => $record->prerequisite !== null)
                                                    ->schema([
                                                        TextEntry::make('prerequisite_name')
                                                            ->label('Nom')
                                                            ->state(fn (Employee $record): ?string => $record->prerequisite?->name),
                                                        TextEntry::make('prerequisite_profession')
                                                            ->label('Profession')
                                                            ->state(fn (Employee $record): ?string => $record->prerequisite?->profession),
                                                        TextEntry::make('prerequisite_employer')
                                                            ->label('Employeur')
                                                            ->state(fn (Employee $record): ?string => $record->prerequisite?->employer?->name),
                                                        TextEntry::make('prerequisite_description')
                                                            ->label('Description')
                                                            ->html()
                                                            ->prose()
                                                            ->columnSpanFull()
                                                            ->state(fn (Employee $record): ?string => $record->prerequisite?->description),
                                                    ])
                                            ),
                                        TextEntry::make('allowance')
                                            ->label('Indemnité'),
                                        TextEntry::make('local_unit')
                                            ->label('Unite locale'),
                                    ]),
                            ]),
                        Tab::make('Santé')
                            ->icon('heroicon-o-heart')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('healthInsurance.name')
                                    ->label('Mutuelle'),
                                TextEntry::make('insurance_affiliation')
                                    ->label('Affiliation mutuelle'),
                                TextEntry::make('emergency_contact')
                                    ->label('Contact en cas d\'urgence')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Notes')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Remarques')
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Compte informatique')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                Section::make('Données partagées avec le module Agent')
                                    ->description('Informations que le module Agent connaît de cet employé.')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('full_name')
                                            ->label('Nom complet')
                                            ->state(fn (Employee $record): string => mb_trim($record->last_name.' '.$record->first_name)),
                                        TextEntry::make('savedEmployer.name')
                                            ->label('Employeur')
                                            ->placeholder('—'),
                                        ImageEntry::make('photo')
                                            ->label('Photo')
                                            ->disk('public')
                                            ->imageHeight(120)
                                            ->defaultImageUrl(fn (Employee $record): string => 'https://ui-avatars.com/api/?size=128&name='.urlencode(mb_trim($record->first_name.' '.$record->last_name))),
                                        TextEntry::make('activeContracts.service.name')
                                            ->label('Services (contrats actifs)')
                                            ->listWithLineBreaks()
                                            ->placeholder('—'),
                                    ]),
                                TextEntry::make('profile.username')
                                    ->label('Nom utilisateur')
                                    ->visible(fn (Employee $record): bool => $record->profile !== null)
                                    ->placeholder('—')
                                    ->suffixAction(RequestProfileChangeAction::make()),
                                TextEntry::make('delete_profile')
                                    ->label('Suppression')
                                    ->state('Demander la suppression du compte informatique.')
                                    ->visible(fn (Employee $record): bool => $record->profile !== null)
                                    ->suffixAction(RequestProfileDeletionAction::make()),
                                TextEntry::make('no_profile')
                                    ->label('Compte informatique')
                                    ->state('Aucun profil informatique pour cet agent.')
                                    ->visible(fn (Employee $record): bool => $record->profile === null)
                                    ->suffixAction(RequestProfileAction::make()),
                            ]),
                        Tab::make('Candidat')
                            ->icon('heroicon-o-identification')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('diploma_level')
                                    ->label('Niveau de diplôme'),
                                TextEntry::make('diploma_nature')
                                    ->label('Nature du diplôme'),
                            ]),
                        Tab::make('Stagiaire')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('intern_type')
                                    ->label('Demande de stage'),
                                TextEntry::make('diploma_level_simplified')
                                    ->label('Niveau de diplôme'),
                                TextEntry::make('candidate_file_name')
                                    ->label('Document du stagiaire')
                                    ->placeholder('—')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                                    ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                                    ->openUrlInNewTab(),
                            ]),
                        Tab::make('Etudiant')
                            ->icon('heroicon-o-academic-cap')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('diploma_nature')
                                    ->label('Nature du diplôme'),
                                TextEntry::make('diploma_level')
                                    ->label('Niveau de diplôme'),
                            ]),
                    ]),
            ]);
    }
}
