<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Schemas;

use AcMarche\Hrm\Models\Evaluation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class EvaluationInfolist
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
                        Section::make('Évaluation')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('direction.name')
                                    ->label('Direction')
                                    ->placeholder('—'),
                                TextEntry::make('result')
                                    ->label('Résultat')
                                    ->badge(),
                            ]),
                        Fieldset::make('Dates')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('evaluation_date')
                                    ->label('Date évaluation')
                                    ->date('d/m/Y'),
                                TextEntry::make('next_evaluation_date')
                                    ->label('Prochaine évaluation')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                                TextEntry::make('validation_date')
                                    ->label('Date de validation')
                                    ->helperText('Validé par le Collège, BP ou CA')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                            ]),
                        Section::make('Notes')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->placeholder('—')
                                    ->columnSpanFull(),
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
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('employee.last_name')
                            ->label('Agent')
                            ->formatStateUsing(
                                fn ($state, Evaluation $record): string => $record->employee?->last_name.' '.$record->employee?->first_name
                            ),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->date('d/m/Y'),
                        TextEntry::make('user_add')
                            ->label('Créé par')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->date('d/m/Y'),
                        TextEntry::make('updated_by')
                            ->label('Modifié par')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
