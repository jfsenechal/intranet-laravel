<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Pages;

use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use AcMarche\Hrm\Models\Process;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Override;

final class ViewProcess extends ViewRecord
{
    /**
     * Redeclared purely to give the property a default, see ViewDeadline.
     */
    #[Override]
    #[Locked]
    public Model|int|string|null $record = null;

    #[Override]
    protected static string $resource = ProcessResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record?->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Retour aux procédures')
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('gray')
                ->url(ProcessResource::getUrl('index')),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            ReplicateAction::make()
                ->icon(Heroicon::Square2Stack)
                ->excludeAttributes([
                    'id',
                    'created_at',
                    'updated_at',
                ])
                ->beforeReplicaSaved(function (Process $replica): void {
                    $replica->name = $replica->name.' (copie)';
                })
                ->successRedirectUrl(fn (Process $replica): string => ProcessResource::getUrl('edit', ['record' => $replica])),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
