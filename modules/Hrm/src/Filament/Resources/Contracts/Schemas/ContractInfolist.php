<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Schemas;

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Section::make('Agent et employeur')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('employer.name')
                                    ->label('Employeur'),
                                TextEntry::make('direction.name')
                                    ->label('Direction'),
                                TextEntry::make('service.name')
                                    ->label('Service'),
                                TextEntry::make('status')
                                    ->label('Statut'),
                            ]),
                        Section::make('Détails du contrat')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('contractType.name')
                                    ->label('Type de contrat'),
                                TextEntry::make('contractNature.name')
                                    ->label('Nature du contrat'),
                                TextEntry::make('payScale.name')
                                    ->label('Échelle'),
                                TextEntry::make('job_title')
                                    ->label('Fonction'),
                                TextEntry::make('status')
                                    ->label('Statut'),
                                TextEntry::make('work_regime')
                                    ->label('Régime de travail (ETP)'),
                                TextEntry::make('hourly_regime')
                                    ->label('Régime horaire'),
                            ]),
                        Section::make('Dates')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Date de début')
                                    ->date('d/m/Y'),
                                TextEntry::make('end_date')
                                    ->label('Date de fin')
                                    ->date('d/m/Y'),
                                TextEntry::make('reminder_date')
                                    ->label('Date de rappel')
                                    ->date('d/m/Y'),
                            ]),
                        Section::make('Documents')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('file1_name')
                                    ->label('Document 1')
                                    ->placeholder('—')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                                    ->url(
                                        fn (?string $state): ?string => $state ? Storage::disk('local')->temporaryUrl(
                                            $state,
                                            now()->addMinutes(5)
                                        ) : null
                                    )
                                    ->openUrlInNewTab(),
                                TextEntry::make('file2_name')
                                    ->label('Document 2')
                                    ->placeholder('—')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                                    ->url(
                                        fn (?string $state): ?string => $state ? Storage::disk('local')->temporaryUrl(
                                            $state,
                                            now()->addMinutes(5)
                                        ) : null
                                    )
                                    ->openUrlInNewTab(),
                            ]),
                        Section::make('College')
                            ->schema([
                                TextEntry::make('college')
                                    ->label('College')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Grid::make()
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        Section::make('Remplacement')
                            ->visible(fn (Contract $record): bool => $record->is_replacement)
                            ->schema([
                                TextEntry::make('replaces.full_name')
                                    ->label('Remplace')
                                    ->hiddenLabel()
                                    ->placeholder('—')
                                    ->icon(Heroicon::OutlinedUser)
                                    ->url(fn (Contract $record): ?string => self::replacedEmployeeUrl($record))
                                    ->color(fn (Contract $record): ?string => self::replacedEmployeeUrl($record) === null ? null : 'primary'),
                            ]),
                        Section::make('Options')
                            ->columns(2)
                            ->schema([
                                IconEntry::make('is_closed')
                                    ->label('Clôturé')
                                    ->boolean(),
                                IconEntry::make('is_amendment')
                                    ->label('Avenant')
                                    ->boolean(),
                                IconEntry::make('is_suspended')
                                    ->label('Suspension')
                                    ->boolean(),
                            ]),
                        Section::make('Métadonnées')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('—'),
                                TextEntry::make('user_add')
                                    ->label('Par')
                                    ->placeholder('—'),
                                TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('—'),
                                TextEntry::make('updated_by')
                                    ->label('Par')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }

    /**
     * The replaced agent is only linked when the viewer is allowed to open their
     * record; otherwise the name is shown as plain text.
     */
    private static function replacedEmployeeUrl(Contract $contract): ?string
    {
        $replaced = $contract->replaces;

        if (! $replaced instanceof Employee) {
            return null;
        }

        if (! Gate::allows('view', $replaced)) {
            return null;
        }

        return ViewEmployee::getUrl(['record' => $replaced], panel: 'hrm-panel');
    }
}
