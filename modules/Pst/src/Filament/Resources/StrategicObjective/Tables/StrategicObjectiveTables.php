<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Tables;

use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use AcMarche\Pst\Models\StrategicObjective;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class StrategicObjectiveTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->forSelectedDepartment()
                    ->with([
                        'oos',
                        'oos.actions',
                        'oos.actions.leaderServices',
                        'oos.actions.partnerServices',
                        'oos.actions.partners',
                        'oos.actions.odds',
                    ])
            )
            ->recordTitleAttribute('name')
            ->recordUrl(fn (StrategicObjective $record): string => StrategicObjectiveResource::getUrl('view', [$record]))
            ->defaultSort('position')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
