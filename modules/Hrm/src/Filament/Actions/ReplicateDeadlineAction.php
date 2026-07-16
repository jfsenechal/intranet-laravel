<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

final class ReplicateDeadlineAction
{
    public static function make(): ReplicateAction
    {
        return ReplicateAction::make()
            ->icon(Heroicon::Square2Stack)
            ->mutateRecordDataUsing(function (array $data): array {
                $data['employee_id'] = null;

                return $data;
            })
            ->schema([
                Select::make('employee_id')
                    ->label('Agent')
                    ->relationship('employee', 'last_name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Employee $record): string => $record->last_name.' '.$record->first_name
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->excludeAttributes([
                'id',
                'created_at',
                'updated_at',
                'user_add',
                'updated_by',
            ])
            ->successRedirectUrl(fn (Deadline $replica): string => DeadlineResource::getUrl('edit', ['record' => $replica]));
    }
}
