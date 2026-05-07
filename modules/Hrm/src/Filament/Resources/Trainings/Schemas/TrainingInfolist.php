<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Trainings\Schemas;

use AcMarche\Hrm\Models\Training;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class TrainingInfolist
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
                        Section::make('Attestation')
                            ->columns(3)
                            ->schema([
                                IconEntry::make('certificate_received')
                                    ->label('Attestation reçue')
                                    ->boolean(),
                                TextEntry::make('certificate_received_at')
                                    ->label('Reçue le')
                                    ->date('d/m/Y'),
                                TextEntry::make('certificate_file')
                                    ->label('Fichier attestation')
                                    ->placeholder('—')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                                    ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                                    ->openUrlInNewTab(),
                            ]),
                        Fieldset::make('Dates')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Date de début')
                                    ->date('d/m/Y'),
                                TextEntry::make('end_date')
                                    ->label('Date de fin')
                                    ->date('d/m/Y'),
                                TextEntry::make('college_date')
                                    ->label('Date collège')
                                    ->date('d/m/Y'),
                                TextEntry::make('reminder_date')
                                    ->label('Date de rappel')
                                    ->date('d/m/Y'),
                            ]),
                        Fieldset::make('Accord')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('granted_by')
                                    ->label('Accordé par'),
                                TextEntry::make('granted_at')
                                    ->label('Accordé le')
                                    ->date('d/m/Y'),
                            ]),
                        Section::make('Description')
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Statut')
                    ->columnSpan(1)
                    ->schema([
                        IconEntry::make('is_closed')
                            ->label('Clôturé')
                            ->boolean(),
                        TextEntry::make('training_type')
                            ->label('Type de formation')
                            ->badge(),
                        TextEntry::make('duration_minutes')
                            ->label('Durée')
                            ->formatStateUsing(fn (?int $state): string => Training::formatDuration($state)),
                    ]),
            ]);
    }
}
