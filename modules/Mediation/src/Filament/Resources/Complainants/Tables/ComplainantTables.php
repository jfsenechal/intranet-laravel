<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Tables;

use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use AcMarche\Mediation\Models\Complainant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ComplainantTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Complainant $record): string => ComplainantResource::getUrl('view', ['record' => $record->id])),

                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salutation')
                    ->label('Civilité')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('Localité')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('case_files_count')
                    ->label('Dossiers')
                    ->counts('caseFiles')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
