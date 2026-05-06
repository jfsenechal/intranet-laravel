<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Pages;

use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Models\Action;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ListActionsPst extends ListRecords
{
    #[Override]
    protected static string $resource = ActionPstResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' actions';
    }

    /**
     * https://github.com/filamentphp/filament/discussions/10803
     *
     * @return array|Tab[]
     */
    public function getTabs(): array
    {
        $tabs = [
            0 => Tab::make('All')
                ->label('Toutes')
                ->badge(fn () => Action::query()->count()),
        ];
        if (auth()->user()->hasRole(RolesEnum::ADMIN->value)) {
            $tabs[1] = Tab::make('NotValidated')
                ->label('Non validées')
                ->badgeColor('warning')
                ->icon('heroicon-m-exclamation-circle')
                ->badge(fn () => Action::query()->notValidated()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->notValidated());
        }
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $tabs[] =
                Tab::make($actionStateEnum->value)
                    ->badge(
                        fn () => Action::query()
                            ->where('state', $actionStateEnum->value)
                            ->validated()
                            ->count()
                    )
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query
                        ->where('state', $actionStateEnum->value)
                        ->validated())
                    ->label($actionStateEnum->getLabel())
                    ->badgeColor($actionStateEnum->getColor())
                    ->icon($actionStateEnum->getIcon());
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une action')
                ->icon('tabler-plus'),
            Actions\Action::make('list-sheet')
                ->label('Liste comme Google sheet')
                ->icon('tabler-list')
                ->url(ActionPstResource::getUrl('asGoogleSheet')),
        ];
    }
}
