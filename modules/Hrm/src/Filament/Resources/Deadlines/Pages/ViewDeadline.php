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
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Override;

final class ViewDeadline extends ViewRecord
{
    /**
     * Redeclared purely to give the property a default.
     *
     * Filament nulls the record out in `InteractsWithRecord::afterActionCalled()` once it no longer
     * exists (the DeleteAction below), and Livewire's `hydrateProperties()` refuses to write null
     * back into a typed property. Without a default the property is then left *uninitialized*, and
     * the next update from that stale snapshot fatals in `getRecord()` instead of returning 404.
     */
    #[Override]
    #[Locked]
    public Model|int|string|null $record = null;

    #[Override]
    protected static string $resource = DeadlineResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Echéance '.$this->record?->name;
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
