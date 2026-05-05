<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use AcMarche\Mediation\Models\Complainant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreateCaseFile extends CreateRecord
{
    #[Override]
    protected static string $resource = CaseFileResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($complainant = $this->getComplainantFromQuery()) {
            return 'Ajouter un dossier pour '.$complainant->last_name.' '.$complainant->first_name;
        }

        return 'Ajouter un dossier';
    }

    protected function fillForm(): void
    {
        $data = [];

        if ($complainant = $this->getComplainantFromQuery()) {
            $data['complainant_id'] = $complainant->id;
        }

        $this->form->fill($data);
    }

    private function getComplainantFromQuery(): ?Complainant
    {
        $complainantId = request()->query('complainant_id');

        return $complainantId ? Complainant::find($complainantId) : null;
    }
}
