<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

final class ReplicateEvaluationAction
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
                'file1_name',
                'file2_name',
            ])
            ->successRedirectUrl(
                fn (Evaluation $replica): string => EvaluationResource::getUrl('view', ['record' => $replica])
            );
    }
}
