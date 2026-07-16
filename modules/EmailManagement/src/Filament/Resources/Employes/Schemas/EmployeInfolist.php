<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Models\Employe;
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
     * IMAP credentials (IMAP_EMPLOYE_*) are not configured, so this returns null and
     * the quota entries degrade to "Quota indisponible" rather than throwing.
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

        $host = config('imap.employe.host');
        $user = config('imap.employe.user');
        $password = config('imap.employe.password');

        if (! is_string($host) || ! is_string($user) || ! is_string($password)) {
            return null;
        }

        return Cache::store('array')->remember(
            'employe-quota:'.$samAccountName,
            60,
            function () use ($host, $user, $password, $samAccountName): ?array {
                try {
                    return (new ImapEmploye($host, $user, $password))->getQuota($samAccountName);
                } catch (Throwable) {
                    // Throwable, not Exception: a misconfigured host surfaces as a
                    // TypeError from ImapEmploye's constructor, which Exception misses.
                    return null;
                }
            },
        );
    }
}
