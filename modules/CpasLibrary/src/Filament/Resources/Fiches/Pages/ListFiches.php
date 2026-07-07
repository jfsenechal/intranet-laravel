<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListFiches extends ListRecords
{
    #[Override]
    protected static string $resource = FicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(
                array_map(
                    fn (FicheTypeEnum $type): Action => Action::make('create_'.$type->value)
                        ->label($type->getLabel())
                        ->icon(Heroicon::Plus)
                        ->url(FicheResource::getUrl('create', ['type' => $type->value])),
                    FicheTypeEnum::cases(),
                ),
            )
                ->label('Nouvelle fiche')
                ->icon(Heroicon::Plus)
                ->button()
                ->visible(fn (): bool => FicheResource::canCreate()),
        ];
    }
}
