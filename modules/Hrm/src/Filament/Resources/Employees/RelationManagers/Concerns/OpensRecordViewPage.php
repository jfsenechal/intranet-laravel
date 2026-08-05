<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Clicking a row of a relation manager — or its view action — opens the record
 * on its own view page instead of the table modal, the way Filament already
 * behaves on resource list pages.
 *
 * The link is only built when the current user may view the record; without it
 * the table falls back to the modal declared by the shared table class.
 */
trait OpensRecordViewPage
{
    /**
     * @param  class-string<ViewRecord>  $viewPage
     */
    protected function openRecordsOnViewPage(Table $table, string $viewPage): Table
    {
        $url = fn (Model $record): ?string => Gate::allows('view', $record)
            ? $viewPage::getUrl(['record' => $record], panel: 'hrm-panel')
            : null;

        $table->getAction(ViewAction::getDefaultName())?->url($url);

        return $table->recordUrl($url);
    }
}
