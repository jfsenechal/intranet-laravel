<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Schemas;

use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Filament\Actions\RequestProfileAction;
use AcMarche\Hrm\Filament\Actions\RequestProfileChangeAction;
use AcMarche\Hrm\Filament\Actions\RequestProfileDeletionAction;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Training;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

final class EmployeeInfolist
{
    public static function configure(Schema $schema, bool $includeComputerAccountTab = true): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Employee')
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(['default' => 12])
                                    ->schema([
                                        ImageEntry::make('photo')
                                            ->label('Photo')
                                            ->disk('public')
                                            ->imageWidth('100%')
                                            ->imageHeight('14rem')
                                            ->extraImgAttributes(['class' => 'min-h-56 h-auto rounded-lg object-contain'])
                                            ->defaultImageUrl(
                                                fn (Employee $record
                                                ): string => 'https://ui-avatars.com/api/?size=256&name='.urlencode(
                                                    mb_trim($record->first_name.' '.$record->last_name)
                                                )
                                            )
                                            ->columnSpan(['default' => 3]),
                                        self::contactDetailsFieldset(),
                                    ]),
                                self::identitySection(),
                            ]),
                        Tab::make('Emploi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                self::activeContractsFieldset(),
                                self::situationFieldset(),
                                self::datesFieldset(),
                                self::payScaleFieldset(),
                                self::prerequisiteFieldset(),
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
                        Tab::make('Formations')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->schema([
                                View::make('hrm::filament.employees.trainings')
                                    ->viewData(fn (Employee $record): array => [
                                        'groups' => self::trainingGroups($record),
                                    ])
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
                            ->visible($includeComputerAccountTab)
                            ->schema([
                                self::sharedWithAgentModuleSection(),
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
                    ]),
            ]);
    }

    private static function identitySection(): Section
    {
        return Section::make('Identité')
            ->columns(2)
            ->schema([
                TextEntry::make('civility')
                    ->label('Civilité'),
                TextEntry::make('birth_date')
                    ->label('Date de naissance')
                    ->date('d/m/Y'),
                TextEntry::make('national_registry_number')
                    ->label('Registre national'),
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        IconEntry::make('show_birthday')
                            ->label('Afficher la date d\' anniversaire')
                            ->helperText('En page d\'accueil et dans qui est qui.')
                            ->boolean(),
                        IconEntry::make('show_photo')
                            ->label('Afficher la photo')
                            ->helperText('En page d\'accueil et dans qui est qui.')
                            ->boolean(),
                    ]),
            ]);
    }

    private static function sharedWithAgentModuleSection(): Section
    {
        return Section::make('Données partagées avec le module Agent')
            ->visible(fn (Employee $record): bool => $record->profile !== null)
            ->description('Informations que le module Agent connaît de cet employé.')
            ->columns(2)
            ->schema([
                TextEntry::make('full_name')
                    ->label('Nom complet')
                    ->state(
                        fn (Employee $record): string => mb_trim(
                            $record->last_name.' '.$record->first_name
                        )
                    ),
                ImageEntry::make('photo')
                    ->label('Photo')
                    ->disk('public')
                    ->imageHeight(120)
                    ->defaultImageUrl(
                        fn (Employee $record): string => 'https://ui-avatars.com/api/?size=128&name='.urlencode(
                            mb_trim($record->first_name.' '.$record->last_name)
                        )
                    ),
                TextEntry::make('activeContracts.service.name')
                    ->label('Services (contrats actifs)')
                    ->listWithLineBreaks()
                    ->placeholder('—'),
            ]);
    }

    private static function contactDetailsFieldset(): Fieldset
    {
        return Fieldset::make('Coordonnées')
            ->columns(2)
            ->columnSpan(['default' => 9])
            ->schema([
                TextEntry::make('address')
                    ->label('Adresse')
                    ->state(
                        fn (Employee $record): string => mb_trim(
                            $record->address.' '.$record->postal_code.' '.$record->city
                        )
                    )
                    ->columnSpanFull(),
                self::privateContactFieldset(),
                self::professionalContactFieldset(),
            ]);
    }

    private static function privateContactFieldset(): Fieldset
    {
        return Fieldset::make('Privé')
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
            ]);
    }

    private static function professionalContactFieldset(): Fieldset
    {
        return Fieldset::make('Professionnel')
            ->columns(1)
            ->schema([
                TextEntry::make('professional_email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope'),
                TextEntry::make('professional_phone')
                    ->label('Téléphone')
                    ->icon('heroicon-o-phone')
                    ->state(
                        fn (Employee $record): ?string => $record->professional_phone === null ? null : mb_trim(
                            $record->professional_phone.($record->professional_phone_extension !== null ? ' (ext. '.$record->professional_phone_extension.')' : '')
                        )
                    ),
                TextEntry::make('professional_mobile')
                    ->label('GSM')
                    ->icon('heroicon-o-device-phone-mobile'),
            ]);
    }

    private static function activeContractsFieldset(): Fieldset
    {
        return Fieldset::make('Contrats actifs')
            ->columns(1)
            ->schema([
                RepeatableEntry::make('activeContracts')
                    ->hiddenLabel()
                    ->placeholder('—')
                    ->schema([
                        TextEntry::make('summary')
                            ->hiddenLabel()
                            ->state(fn (Contract $record): string => self::contractSummary($record))
                            ->url(fn (Contract $record): ?string => self::contractUrl($record))
                            ->color(fn (Contract $record): ?string => self::contractUrl($record) === null ? null : 'primary'),
                        TextEntry::make('replaces')
                            ->label('Remplace')
                            ->visible(fn (Contract $record): bool => $record->replaces instanceof Employee)
                            ->state(fn (Contract $record): ?string => $record->replaces?->full_name)
                            ->icon(Heroicon::OutlinedUser)
                            ->url(fn (Contract $record): ?string => self::employeeUrl($record->replaces))
                            ->color(fn (Contract $record): ?string => self::employeeUrl($record->replaces) === null ? null : 'primary'),
                    ]),
            ]);
    }

    private static function situationFieldset(): Fieldset
    {
        return Fieldset::make('Situation')
            ->columns(3)
            ->schema([
                TextEntry::make('job_title')
                    ->label('Fonction')
                    ->helperText('Fonction encodée sur la fiche'),
                TextEntry::make('status')
                    ->label('Statut')
                    ->badge(),
                IconEntry::make('is_archived')
                    ->label('Archivé')
                    ->boolean(),
                IconEntry::make('is_new_hire')
                    ->label('Nouvel agent')
                    ->boolean(),
            ]);
    }

    private static function datesFieldset(): Fieldset
    {
        return Fieldset::make('Dates')
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
            ]);
    }

    private static function payScaleFieldset(): Fieldset
    {
        return Fieldset::make('Barème')
            ->columns(3)
            ->schema([
                TextEntry::make('payScale.name')
                    ->label('Echelle'),
                TextEntry::make('pay_scale_code')
                    ->label('Code barème'),
                TextEntry::make('allowance')
                    ->label('Indemnité'),
                TextEntry::make('local_unit')
                    ->label('Unite locale'),
            ]);
    }

    private static function prerequisiteFieldset(): Fieldset
    {
        return Fieldset::make('Prérequis pour l\'évolution de carrière')
            ->columns(3)
            ->schema([
                TextEntry::make('prerequisite.name')
                    ->label('Nom')
                    ->placeholder('—'),
                TextEntry::make('prerequisite.profession')
                    ->label('Profession')
                    ->placeholder('—'),
                TextEntry::make('prerequisite.employer.name')
                    ->label('Employeur')
                    ->placeholder('—'),
                TextEntry::make('prerequisite.description')
                    ->label('Description')
                    ->html()
                    ->prose()
                    ->columnSpanFull()
                    ->placeholder('—'),
            ]);
    }

    /**
     * Trainings of the employee grouped by type, following the enum declaration order.
     * Types without any training are omitted.
     *
     * @return array<int, array{type: TrainingTypeEnum, trainings: \Illuminate\Support\Collection<int, Training>, urls: array<int, string|null>, total: int}>
     */
    private static function trainingGroups(Employee $employee): array
    {
        $trainings = $employee->trainings()
            ->orderBy('start_date')
            ->get()
            ->groupBy(fn (Training $training): string => $training->training_type->value);

        $groups = [];
        foreach (TrainingTypeEnum::cases() as $type) {
            $ofType = $trainings->get($type->value);
            if ($ofType === null || $ofType->isEmpty()) {
                continue;
            }

            $groups[] = [
                'type' => $type,
                'trainings' => $ofType,
                'urls' => $ofType
                    ->mapWithKeys(fn (Training $training): array => [
                        $training->id => self::trainingUrl($training),
                    ])
                    ->all(),
                'total' => (int) $ofType->sum('duration_minutes'),
            ];
        }

        return $groups;
    }

    private static function contractSummary(Contract $contract): string
    {
        return implode(' • ', array_filter([
            $contract->service?->name,
            $contract->payScale?->name,
            $contract->job_title,
            $contract->contractNature?->name,
            $contract->contractType?->name,
            $contract->hourly_regime,
        ]));
    }

    /**
     * The infolist is also rendered outside the HRM panel, so resource URLs are
     * resolved against the panel that owns them and only linked when the viewer
     * is allowed to open the record.
     */
    private static function contractUrl(Contract $contract): ?string
    {
        if (! Gate::allows('view', $contract)) {
            return null;
        }

        return ViewContract::getUrl(['record' => $contract], panel: 'hrm-panel');
    }

    private static function trainingUrl(Training $training): ?string
    {
        if (! Gate::allows('view', $training)) {
            return null;
        }

        return ViewTraining::getUrl(['record' => $training], panel: 'hrm-panel');
    }

    private static function employeeUrl(?Employee $employee): ?string
    {
        if (! $employee instanceof Employee) {
            return null;
        }

        if (! Gate::allows('view', $employee)) {
            return null;
        }

        return ViewEmployee::getUrl(['record' => $employee], panel: 'hrm-panel');
    }
}
