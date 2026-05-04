<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Tables;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\AgreementTypeResource;
use AcMarche\Mediation\Models\AgreementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class AgreementTypeTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (AgreementType $record): string => AgreementTypeResource::getUrl('view', ['record' => $record->id])),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
