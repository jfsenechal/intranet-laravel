<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HrDocuments\Tables;

use AcMarche\Hrm\Models\HrDocument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

final class HrDocumentTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (HrDocument $record): string => $record->employee
                            ? $record->employee->last_name.' '.$record->employee->first_name
                            : '—'
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('file_name')
                    ->label('Fichier')
                    ->formatStateUsing(fn (?string $state): string => $state ? '✓' : '—')
                    ->url(
                        fn (?string $state): ?string => $state
                            ? Storage::disk('public')->url(config('hrm.uploads.documents').'/'.$state)
                            : null,
                        shouldOpenInNewTab: true,
                    )
                    ->toggleable(),
                TextColumn::make('updated_by')
                    ->label('Modifié par')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
