<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Tables;

use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use AcMarche\Pst\Models\OperationalObjective;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class OperationalObjectiveTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->forSelectedDepartment()
                    ->with([
                        'actions',
                        'actions.leaderServices',
                        'actions.partnerServices',
                        'actions.partners',
                        'actions.odds',
                    ])
            )
            ->recordUrl(fn (OperationalObjective $record): string => OperationalObjectiveResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('position')
                    ->label('Numéro')
                    ->state(
                        fn (OperationalObjective $objective
                        ): string => $objective->strategicObjective?->position.'.'.' '.$objective->position
                    )->toggleable()
                    ->sortable(),
                TextColumn::make('os')
                    ->label('Os')
                    ->state(fn (): string => 'Os')
                    ->tooltip(function (TextColumn $column): ?string {
                        $record = $column->getRecord();

                        return $record->strategicObjective?->name;
                    })
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->icon(fn (OperationalObjective $record): Heroicon|false => $record->isInternal() ? Heroicon::LightBulb : false)
                    ->iconPosition(IconPosition::After)
                    ->suffix(fn (OperationalObjective $record): string => $record->isInternal() ? '(Interne)' : '')
                    ->sortable()
                    ->limit(85)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('actions_count')
                    ->label('Nbre actions')
                    ->counts('actions')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('isInternal')
                    ->label('Interne')
                    ->state(fn (OperationalObjective $record): string => $record->isInternal() ? 'Oui' : 'Non')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make()
                    ->icon('tabler-edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function tableInline(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('actions_count')
                    ->label('Actions')
                    ->counts('actions'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un Oo')
                    ->icon('tabler-plus'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn (OperationalObjective $record): string => OperationalObjectiveResource::getUrl(
                            'view',
                            ['record' => $record]
                        )
                    ),
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
