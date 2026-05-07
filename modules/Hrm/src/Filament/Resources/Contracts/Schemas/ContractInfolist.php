<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
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
                Fieldset::make('Dates')
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
                Fieldset::make('Remplacement')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('is_replacement')
                            ->label('Remplacement'),
                        TextEntry::make('replaces.full_name')
                            ->label('Remplace'),
                    ]),
                Fieldset::make('Options')
                    ->columns(4)
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
                Section::make('Documents')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('file1_name')
                            ->label('Document 1')
                            ->placeholder('—')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                            ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                            ->openUrlInNewTab(),
                        TextEntry::make('file2_name')
                            ->label('Document 2')
                            ->placeholder('—')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                            ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                            ->openUrlInNewTab(),
                    ]),
                Section::make('College')
                    ->schema([
                        TextEntry::make('college')
                            ->label('College')
                            ->hiddenLabel()
                            ->html()
                            // Note e($state) escapes HTML first (security), then nl2br() adds <br> tags. If the stored value already contains HTML you want to keep, drop the e():
                            // ->formatStateUsing(fn (?string $state): ?string => nl2br($state ?? ''))
                            ->formatStateUsing(fn (?string $state): ?string => $state ? nl2br(e($state)) : null)
                            ->prose()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
