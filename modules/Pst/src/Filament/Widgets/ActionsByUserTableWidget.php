<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Widgets;

use AcMarche\Pst\Filament\Resources\ActionPst\Tables\ActionTables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Override;

final class ActionsByUserTableWidget extends BaseWidget
{
    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        $table
            ->heading('Actions vous concernant')
            ->description('Vous êtes repris comme agent pilote')
            ->query(
                fn () => auth()->user()->actions()->getQuery()
            );

        return ActionTables::actionsForWidget($table);
    }
}
