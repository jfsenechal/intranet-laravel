<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas;

use AcMarche\EmailManagement\Imap\ImapCitoyen;
use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The token hashes (auth_token, remember_token) are deliberately absent: they are hidden on
 * the model and must never be rendered.
 */
final class CitoyenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Grid::make(1)
                        ->schema([
                            Section::make('Identité')
                                ->columns(3)
                                ->components([
                                    TextEntry::make('givenName')
                                        ->label('Prénom')
                                        ->placeholder('-'),
                                    TextEntry::make('sn')
                                        ->label('Nom')
                                        ->placeholder('-'),
                                    TextEntry::make('employeeNumber')
                                        ->label('Numéro national')
                                        ->placeholder('-'),
                                ]),
                            Section::make('Messagerie')
                                ->columns(2)
                                ->components([
                                    TextEntry::make('virtualUser.quota_bytes')
                                        ->label('Quota')
                                        ->numeric()
                                        ->suffix(' Mo')
                                        ->placeholder('-'),
                                    TextEntry::make('homeDirectory')
                                        ->label('Répertoire'),
                                ]),
                            Section::make('Utilisation boîte mail')
                                ->columns()
                                ->components([
                                    TextEntry::make('quota_usage')
                                        ->label('Utilisation')
                                        ->state(function (Citoyen $record): string {
                                            $quotaInfo = self::getQuotaInfo($record->mail);

                                            if ($quotaInfo === null) {
                                                return 'Quota indisponible';
                                            }

                                            $usageMo = round($quotaInfo['usage'] / 1024, 2);
                                            $limitMo = round($quotaInfo['limit'] / 1024, 2);

                                            return "{$usageMo} Mo / {$limitMo} Mo ({$quotaInfo['pourcentage']}%)";
                                        }),
                                    TextEntry::make('quota_percentage')
                                        ->label('Pourcentage utilisé')
                                        ->state(function (Citoyen $record): string {
                                            $quotaInfo = self::getQuotaInfo($record->mail);

                                            return $quotaInfo !== null ? $quotaInfo['pourcentage'].'%' : '-';
                                        })
                                        ->badge()
                                        ->color(function (Citoyen $record): string {
                                            $quotaInfo = self::getQuotaInfo($record->mail);

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
                            Section::make('Adresse')
                                ->columns(3)
                                ->components([
                                    TextEntry::make('postalAddress')
                                        ->label('Rue')
                                        ->placeholder('-'),
                                    TextEntry::make('postalCode')
                                        ->label('Code postal')
                                        ->placeholder('-'),
                                    TextEntry::make('l')
                                        ->label('Localité')
                                        ->placeholder('-'),
                                ]),
                            Section::make('Récupération du compte')
                                ->columns()
                                ->components([
                                    TextEntry::make('recovery_email')
                                        ->label('Email de récupération')
                                        ->placeholder('-')
                                        ->copyable(),
                                    TextEntry::make('recovery_phone')
                                        ->label('Téléphone de récupération')
                                        ->placeholder('-'),
                                    TextEntry::make('charter_accepted_at')
                                        ->label('Charte acceptée le')
                                        ->dateTime()
                                        ->placeholder('Non acceptée'),
                                ]),
                            TextEntry::make('description')
                                ->label('Description')
                                ->hiddenLabel()
                                ->placeholder('-')
                                ->columnSpanFull(),

                        ]),
                    Section::make('État')
                        ->components([
                            IconEntry::make('virtualUser.active')
                                ->label('Actif')
                                ->boolean()
                                ->placeholder('-'),
                            TextEntry::make('last_connection')
                                ->label('Dernière connexion')
                                ->date()
                                ->placeholder('Jamais'),
                            TextEntry::make('created_at')
                                ->label('Créé le')
                                ->dateTime(),
                            TextEntry::make('updated_at')
                                ->label('Modifié le')
                                ->dateTime(),
                        ])
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Returns null when IMAP is unconfigured or unreachable, so the quota entries degrade
     * to "Quota indisponible" rather than throwing.
     *
     * Cached in the array store rather than a static property, because Octane keeps statics
     * across requests. A failure is stored as false: remember() does not keep null, so each
     * entry of the page would otherwise wait for its own connection timeout.
     *
     * @return array{usage: int, limit: int, pourcentage: string}|null
     */
    private static function getQuotaInfo(string $mail): ?array
    {
        $imap = ImapCitoyen::fromConfig();

        if (! $imap->isConfigured()) {
            return null;
        }

        $quotaInfo = Cache::store('array')->remember(
            'citoyen-quota:'.$mail,
            60,
            function () use ($imap, $mail): array|false {
                try {
                    return $imap->getQuota($mail) ?: false;
                } catch (Throwable $e) {
                    report($e);

                    return false;
                }
            },
        );

        return $quotaInfo ?: null;
    }
}
