<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Pages;

use AcMarche\Hrm\Filament\Actions\BackToEmployeeAction;
use AcMarche\Hrm\Filament\Actions\ReplicateDeadlineAction;
use AcMarche\Hrm\Filament\Resources\Deadlines\DeadlineResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
            ReplicateDeadlineAction::make(),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
