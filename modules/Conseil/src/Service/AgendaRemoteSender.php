<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Mirrors the agenda file on the legacy remote server.
 *
 * Replaces the legacy data/RequestHelper.php helper.
 */
final class AgendaRemoteSender
{
    private readonly string $baseUrl;

    private readonly int $timeout;

    public function __construct()
    {
        $this->baseUrl = mb_rtrim((string) config('conseil.remote.base_url'), '/').'/';
        $this->timeout = (int) config('conseil.remote.timeout', 30);
    }

    /**
     * Upload the agenda file (stored on the public disk) to the remote server.
     *
     * @throws RuntimeException
     */
    public function send(string $path): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw new RuntimeException("Le fichier {$path} est introuvable.");
        }

        $fileName = basename($path);

        try {
            $response = Http::timeout($this->timeout)
                ->attach('file_field', $disk->get($path), $fileName)
                ->post($this->baseUrl.'ordre/add.php', [
                    'file_name' => $fileName,
                ]);
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                "Impossible de joindre le serveur distant: {$throwable->getMessage()}",
                previous: $throwable,
            );
        }

        $this->guardAgainstError($response->body());
    }

    /**
     * Remove the agenda file from the remote server.
     *
     * @throws RuntimeException
     */
    public function delete(string $path): void
    {
        $fileName = basename($path);

        try {
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->baseUrl.'ordre/delete.php', [
                    'file_name' => $fileName,
                ]);
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                "Impossible de joindre le serveur distant: {$throwable->getMessage()}",
                previous: $throwable,
            );
        }

        $this->guardAgainstError($response->body());
    }

    /**
     * @throws RuntimeException
     */
    private function guardAgainstError(string $body): void
    {
        $result = json_decode($body, false);

        if (! $result) {
            throw new RuntimeException('Erreur du serveur distant: '.$body);
        }

        if (property_exists($result, 'error') && $result->error !== null) {
            throw new RuntimeException((string) $result->error);
        }
    }
}
