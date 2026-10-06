<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Schemas;

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identité')
                    ->columns(12)
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('Photo')
                            ->hiddenLabel()
                            ->circular()
                            ->imageSize(160)
                            ->state(fn (Profile $record): string => self::photoUrl($record))
                            ->columnSpan(3),
                        Fieldset::make('Coordonnées')
                            ->columns(2)
                            ->columnSpan(9)
                            ->schema([
                                TextEntry::make('username')->label('Identifiant')->copyable(),
                                TextEntry::make('ldap_email')
                                    ->label('Email professionnel')
                                    ->state(fn (Profile $record): ?string => LdapRepository::emailByUsername(
                                        $record->username
                                    ))
                                    ->copyable()
                                    ->placeholder('Aucune adresse dans la LDAP'),
                                TextEntry::make('employee.hired_at')
                                    ->label('Date d\'entrée')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                                TextEntry::make('employee.left_at')
                                    ->label('Date de sortie')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                                TextEntry::make('location')->label('Emplacement'),
                                TextEntry::make('employee_id')->label('Matricule RH')->helperText('Id db'),
                                IconEntry::make('no_mail')->label('Pas de mail professionnel nécessaire')
                                    ->visible(fn (Model $record) => $record->no_mail === true),
                                TextEntry::make('notes')->label('Remarques')->columnSpanFull(),
                            ]),
                        self::activeContractsFieldset()
                            ->visible(fn (Profile $record): bool => $record->employee !== null)
                            ->columnSpanFull(),
                    ]),
                Section::make('Accès')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('emails')
                            ->label('Mailboxes partagées')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->copyable()
                            ->placeholder('Aucune mailbox partagée'),
                        TextEntry::make('supervisors')
                            ->label('Responsables')
                            ->state(fn (Profile $record): array => $record->supervisorNames())
                            ->listWithLineBreaks(),
                    ]),
                Grid::make(2)->schema([
                    Section::make('Matériel')
                        ->relationship('hardware')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('existing_pc')->label('PC existant'),
                            TextEntry::make('new_pc')->label('Nouveau PC'),
                            IconEntry::make('vpn')->label('VPN'),
                            TextEntry::make('other')->label('Autre'),
                        ]),
                    Section::make('Téléphonie')
                        ->relationship('phone')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('existing_number')->label('Numéro existant'),
                            TextEntry::make('mobile_number')->label('Numéro mobile'),
                            IconEntry::make('new_number')->label('Nouveau numéro'),
                            IconEntry::make('external_number')->label('Numéro extérieur'),
                        ]),
                ]),
            ]);
    }

    /**
     * Read-only summary of the linked HRM employee's active contracts, without
     * links to the HRM panel.
     */
    private static function activeContractsFieldset(): Fieldset
    {
        return Fieldset::make('Contrats actifs')
            ->columns(1)
            ->schema([
                RepeatableEntry::make('employee.activeContracts')
                    ->hiddenLabel()
                    ->placeholder('—')
                    ->schema([
                        TextEntry::make('summary')
                            ->hiddenLabel()
                            ->state(fn (Contract $record): string => self::contractSummary($record)),
                        TextEntry::make('replaces')
                            ->label('Remplace')
                            ->visible(fn (Contract $record): bool => $record->replaces instanceof Employee)
                            ->state(fn (Contract $record): ?string => $record->replaces?->full_name)
                            ->icon(Heroicon::OutlinedUser),
                    ]),
            ]);
    }

    private static function contractSummary(Contract $contract): string
    {
        return implode(' • ', array_filter([
            $contract->service?->name,
            $contract->job_title,
            $contract->contractType?->name,
        ]));
    }

    /**
     * Same photo as the WhoIsWho directory when the profile is linked to an
     * HRM employee; otherwise the avatar of the User sharing the username,
     * falling back to a generated initials avatar.
     */
    private static function photoUrl(Profile $profile): string
    {
        if ($profile->employee !== null) {
            return EmployeeRepository::photoUrl($profile->employee);
        }

        if (filled($profile->user?->avatar_url)) {
            return Storage::disk('public')->url($profile->user->avatar_url);
        }

        return 'https://ui-avatars.com/api/?size=160&name='.urlencode(
            mb_trim($profile->first_name.' '.$profile->last_name)
        );
    }
}
