<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service\RelationManagers;

use AcMarche\Pst\Filament\Resources\ActionPst\Tables\ActionTables;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\Service;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ActionsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'leadingActions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        /** @var Service $ownerRecord */
        return $ownerRecord->actionsForDepartment()->count().' Actions liées';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ActionTables::actionsInline($table)
            ->modifyQueryUsing(fn (): Builder => $this->getActionsQuery());
    }

    /**
     * @return Builder<Action>
     */
    private function getActionsQuery(): Builder
    {
        /** @var Service $service */
        $service = $this->ownerRecord;

        return $service->actionsForDepartment();
    }
}
