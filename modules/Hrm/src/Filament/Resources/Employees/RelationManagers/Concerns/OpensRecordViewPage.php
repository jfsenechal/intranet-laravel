<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Clicking a row of a relation manager — or its view action — opens the record
 * on its own view page instead of the table modal, the way Filament already
 * behaves on resource list pages. The edit action likewise opens the edit page
 * of the same resource, when it has one.
 *
 * Each link is only built when the current user may perform the action on the
 * record; without it the table falls back to the modal declared by the shared
 * table class.
 */
trait OpensRecordViewPage
{
    /**
     * @param  class-string<ViewRecord>  $viewPage
     */
    protected function openRecordsOnViewPage(Table $table, string $viewPage): Table
    {
        $resource = $viewPage::getResource();

        $url = fn (Model $record): ?string => Gate::allows('view', $record)
            ? $viewPage::getUrl(['record' => $record], panel: 'hrm-panel')
            : null;

        $table->getAction(ViewAction::getDefaultName())?->url($url);

        if ($resource::hasPage('edit')) {
            $table->getAction(EditAction::getDefaultName())?->url(
                fn (Model $record): ?string => Gate::allows('update', $record)
                    ? $resource::getUrl('edit', ['record' => $record], panel: 'hrm-panel')
                    : null,
            );
        }

        return $table->recordUrl($url);
    }
}
