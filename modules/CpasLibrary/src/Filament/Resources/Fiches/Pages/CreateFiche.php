<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateFiche extends CreateRecord
{
    #[Override]
    protected static string $resource = FicheResource::class;

    #[Override]
    public function mount(): void
    {
        parent::mount();

        $type = FicheTypeEnum::tryFrom((string) request()->query('type'));

        if ($type instanceof FicheTypeEnum) {
            $this->form->fill(['type' => $type->value]);
        }
    }

    public function getTitle(): string
    {
        $type = FicheTypeEnum::tryFrom((string) request()->query('type'));

        return $type instanceof FicheTypeEnum
            ? 'Nouvelle fiche — '.$type->getLabel()
            : 'Nouvelle fiche';
    }
}
