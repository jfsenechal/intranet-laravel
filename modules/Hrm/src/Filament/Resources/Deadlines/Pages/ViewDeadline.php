<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Pages;

use AcMarche\Hrm\Filament\Actions\BackToEmployeeAction;
use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewDeadline extends ViewRecord
{
    #[Override]
    protected static string $resource = DeadlineResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Echéance '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            BackToEmployeeAction::make(),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            ReplicateAction::make()
                ->icon(Heroicon::Square2Stack)
                ->schema([
                    Select::make('employee_id')
                        ->label('Agent')
                        ->relationship('employee', 'last_name')
                        ->getOptionLabelFromRecordUsing(
                            fn (Employee $record): string => $record->last_name.' '.$record->first_name
                        )
                        ->searchable()
                        ->preload(),
                ])
                ->excludeAttributes([
                    'id',
                    'created_at',
                    'updated_at',
                    'user_add',
                    'updated_by',
                ])
                ->successRedirectUrl(fn (Deadline $replica): string => DeadlineResource::getUrl('edit', ['record' => $replica])),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
