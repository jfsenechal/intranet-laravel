<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\ListLdapRepository;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class EmployeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns()
                    ->components([
                        TextEntry::make('givenName')
                            ->label('Prénom'),
                        TextEntry::make('sn')
                            ->label('Nom'),
                        TextEntry::make('cn')
                            ->label('Nom complet'),
                        TextEntry::make('samaccountname')
                            ->label('Identifiant'),
                        TextEntry::make('displayName')
                            ->label("Nom d'affichage"),
                    ]),
                Section::make('Coordonnées')
                    ->columns()
                    ->components([
                        TextEntry::make('mail')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('telephoneNumber')
                            ->label('Téléphone')
                            ->placeholder('-'),
                    ]),
                Section::make('Membre de')
                    ->description("Les groupes de l'annuaire qui délivrent vers cette adresse")
                    ->columns()
                    ->components([
                        TextEntry::make('member_of_lists')
                            ->label(ListOuEnum::LISTS->getLabel())
                            ->state(fn (Employe $record): array => self::memberOfLists($record->mail, ListOuEnum::LISTS))
                            ->badge()
                            ->color('gray')
                            ->placeholder('Aucune'),
                        TextEntry::make('member_of_services')
                            ->label(ListOuEnum::SERVICES->getLabel())
                            ->state(fn (Employe $record): array => self::memberOfLists($record->mail, ListOuEnum::SERVICES))
                            ->badge()
                            ->color('gray')
                            ->placeholder('Aucun'),
                    ]),
                Section::make('Connexion')
                    ->columns()
                    ->components([
                        TextEntry::make('last_connection')
                            ->label('Dernière connexion')
                            ->date(),
                        TextEntry::make('protocol_connection')
                            ->label('Protocole'),
                        TextEntry::make('port_connection')
                            ->label('Port')
                            ->numeric(),
                        IconEntry::make('secure_connection')
                            ->label('Sécurisé')
                            ->boolean(),
                    ]),
                Section::make('Utilisation boîte mail')
                    ->columns()
                    ->components([
                        TextEntry::make('quota_usage')
                            ->label('Utilisation')
                            ->state(function (Employe $record): string {
                                $quotaInfo = self::getQuotaInfo($record->samaccountname);

                                if ($quotaInfo === null) {
                                    return 'Quota indisponible';
                                }

                                $usageMo = round($quotaInfo['usage'] / 1024, 2);
                                $limitMo = round($quotaInfo['limit'] / 1024, 2);

                                return "{$usageMo} Mo / {$limitMo} Mo ({$quotaInfo['pourcentage']}%)";
                            }),
                        TextEntry::make('quota_percentage')
                            ->label('Pourcentage utilisé')
                            ->state(function (Employe $record): string {
                                $quotaInfo = self::getQuotaInfo($record->samaccountname);

                                return $quotaInfo !== null ? $quotaInfo['pourcentage'].'%' : '-';
                            })
                            ->badge()
                            ->color(function (Employe $record): string {
                                $quotaInfo = self::getQuotaInfo($record->samaccountname);

                                if ($quotaInfo === null) {
                                    return 'gray';
                                }

                                return match (true) {
                                    (float) $quotaInfo['pourcentage'] >= 90 => 'danger',
                                    (float) $quotaInfo['pourcentage'] >= 70 => 'warning',
                                    default => 'success',
                                };
                            }),
                    ]),
                Section::make('Divers')
                    ->components([
                        TextEntry::make('dn')
                            ->label('DN')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('sync_at')
                            ->label('Synchronisé le')
                            ->dateTime()
                            ->placeholder('Jamais'),
                    ]),
            ]);
    }

    /**
     * The addresses of the groups of one OU that deliver to this address.
     *
     * A group is named by its own mail when it has one, and by its cn otherwise, the way the
     * list page names them. Returns an empty array when the employe has no address or the
     * directory is unreachable, so the entry renders its placeholder rather than throwing.
     *
     * Cached the same way and for the same reason as the quota: the page asks once per OU per
     * render, and a static cache would outlive the request under Octane.
     *
     * @return array<int, string>
     */
    private static function memberOfLists(?string $mail, ListOuEnum $ou): array
    {
        if ($mail === null || $mail === '') {
            return [];
        }

        return Cache::store('array')->remember(
            "employe-member-of:{$ou->value}:{$mail}",
            60,
            function () use ($mail, $ou): array {
                try {
                    return app(ListLdapRepository::class)
                        ->memberOfLists($ou, $mail)
                        ->map(fn (ListAliasLdap $entry): string => $entry->getFirstAttribute('mail') ?? $entry->getFirstAttribute('cn'))
                        ->sort()
                        ->values()
                        ->all();
                } catch (Throwable) {
                    return [];
                }
            },
        );
    }

    /**
     * Returns null when IMAP is unconfigured or unreachable, so the quota entries degrade
     * to "Quota indisponible" rather than throwing.
     *
     * Cached in the array store rather than a static property: this application runs
     * Octane, where static state persists across requests and would leak one employe's
     * quota into another's page.
     *
     * @return array{usage: int, limit: int, pourcentage: string}|null
     */
    private static function getQuotaInfo(?string $samAccountName): ?array
    {
        if ($samAccountName === null || $samAccountName === '') {
            return null;
        }

        $imap = ImapEmploye::fromConfig();

        if (! $imap->isConfigured()) {
            return null;
        }

        return Cache::store('array')->remember(
            'employe-quota:'.$samAccountName,
            60,
            function () use ($imap, $samAccountName): ?array {
                try {
                    return $imap->getQuota($samAccountName);
                } catch (Throwable) {
                    return null;
                }
            },
        );
    }
}
