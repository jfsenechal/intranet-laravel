<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Pages;

use AcMarche\Hrm\Filament\Actions\BackToEmployeeAction;
use AcMarche\Hrm\Filament\Resources\Contracts\ContractResource;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewContract extends ViewRecord
{
    #[Override]
    protected static string $resource = ContractResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Contrat de '.$this->record->employee->last_name.' '.$this->record->employee->first_name;
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
                ->successRedirectUrl(fn (Contract $replica): string => ContractResource::getUrl('edit', ['record' => $replica])),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
