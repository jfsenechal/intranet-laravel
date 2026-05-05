<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Offenses\Models\Offender;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class CreateOffense extends CreateRecord
{
    #[Override]
    protected static string $resource = OffenseResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($offender = $this->getOffenderFromQuery()) {
            return 'Ajouter une incivilité pour '.$offender->last_name.' '.$offender->first_name;
        }

        abort(404, 'Contrevenant introuvable');
    }

    protected function fillForm(): void
    {
        $data = [];

        if ($offender = $this->getOffenderFromQuery()) {
            $data['offender_id'] = $offender->id;
        }

        $this->form->fill($data);
    }

    private function getOffenderFromQuery(): ?Offender
    {
        $offenderId = request()->query('offender_id');

        return $offenderId ? Offender::find($offenderId) : null;
    }
}
