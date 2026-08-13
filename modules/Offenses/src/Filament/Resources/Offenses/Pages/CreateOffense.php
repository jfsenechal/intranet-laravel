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
    /**
     * Kept as a Livewire property: the `offender_id` query string is only present on the initial
     * page load, not on the subsequent Livewire update requests (file uploads, live fields, save).
     */
    public ?int $offenderId = null;

    #[Override]
    protected static string $resource = OffenseResource::class;

    private ?Offender $offender = null;

    #[Override]
    public function mount(): void
    {
        $offenderId = request()->query('offender_id');
        $this->offenderId = $offenderId ? (int) $offenderId : null;

        abort_unless($this->getOffender() instanceof Offender, 404, 'Contrevenant introuvable');

        parent::mount();
    }

    public function getTitle(): string|Htmlable
    {
        if (($offender = $this->getOffender()) instanceof Offender) {
            return 'Ajouter une incivilité pour '.$offender->last_name.' '.$offender->first_name;
        }

        return 'Ajouter une incivilité';
    }

    protected function fillForm(): void
    {
        $data = [];

        if (($offender = $this->getOffender()) instanceof Offender) {
            $data['offender_id'] = $offender->id;
        }

        $this->form->fill($data);
    }

    private function getOffender(): ?Offender
    {
        if ($this->offender instanceof Offender) {
            return $this->offender;
        }

        return $this->offender = $this->offenderId ? Offender::find($this->offenderId) : null;
    }
}
