<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Observers;

use AcMarche\Conseil\Models\Agenda;
use AcMarche\Conseil\Service\AgendaRemoteSender;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Keeps the agenda file synchronised with the legacy remote server.
 */
final class AgendaObserver
{
    public function __construct(private readonly AgendaRemoteSender $remoteSender) {}

    public function created(Agenda $agenda): void
    {
        if (filled($agenda->file_name)) {
            $this->run(fn () => $this->remoteSender->send($agenda->file_name));
        }
    }

    public function updated(Agenda $agenda): void
    {
        if (! $agenda->wasChanged('file_name')) {
            return;
        }

        $previousPath = $agenda->getOriginal('file_name');

        $this->run(function () use ($agenda, $previousPath): void {
            if (filled($previousPath) && $previousPath !== $agenda->file_name) {
                $this->remoteSender->delete($previousPath);
            }

            if (filled($agenda->file_name)) {
                $this->remoteSender->send($agenda->file_name);
            }
        });
    }

    public function deleted(Agenda $agenda): void
    {
        if (filled($agenda->file_name)) {
            $this->run(fn () => $this->remoteSender->delete($agenda->file_name));
        }
    }

    /**
     * Run a remote operation, surfacing failures as a Filament notification
     * instead of aborting the request.
     */
    private function run(callable $operation): void
    {
        try {
            $operation();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Synchronisation du fichier échouée')
                ->body($throwable->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
