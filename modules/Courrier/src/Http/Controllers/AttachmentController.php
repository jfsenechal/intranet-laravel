<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Http\Controllers;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Repository\ImapRepository;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentController extends Controller
{
    public function __construct(
        private readonly ImapRepository $imapRepository
    ) {}

    public function show(int $uid, int $index): StreamedResponse|Response
    {
        try {
            $imapRepository = $this->resolveImapRepository();
            $attachment = $imapRepository->getAttachment($uid, $index);

            return $imapRepository->createAttachmentDownloadResponse($attachment);
        } catch (ImapException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function preview(int $uid, int $index): StreamedResponse|Response
    {
        try {
            $attachment = $this->resolveImapRepository()->getAttachment($uid, $index);
            $stream = $attachment->contentStream();
            $mimeType = $attachment->contentType() ?? 'application/octet-stream';

            return new StreamedResponse(function () use ($stream): void {
                $outputStream = fopen('php://output', 'wb');

                if ($outputStream === false) {
                    return;
                }

                while (! $stream->eof()) {
                    fwrite($outputStream, $stream->read(8192));
                    flush();
                }

                fclose($outputStream);

                if (method_exists($stream, 'close')) {
                    $stream->close();
                }
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
            ]);
        } catch (ImapException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function download(Attachment $attachment): BinaryFileResponse|Response
    {
        Gate::authorize('download', $attachment);

        $disk = Storage::disk(config('courrier.storage.disk'));
        $path = config('courrier.storage.directory')."/attachments/{$attachment->file_name}";

        if (! $disk->exists($path)) {
            return response('Fichier non trouvé', 404);
        }

        return response()->download($disk->path($path), $attachment->file_name, [
            'Content-Type' => $attachment->mime,
        ]);
    }

    public function previewStored(Attachment $attachment): BinaryFileResponse|Response
    {
        Gate::authorize('download', $attachment);

        $disk = Storage::disk(config('courrier.storage.disk'));
        $path = config('courrier.storage.directory')."/attachments/{$attachment->file_name}";

        if (! $disk->exists($path)) {
            return response('Fichier non trouvé', 404);
        }

        return response()->file($disk->path($path), [
            'Content-Type' => $attachment->mime,
        ]);
    }

    /**
     * Resolve the IMAP repository for the mailbox requested via the `mailbox`
     * query parameter, validated against the known department mailboxes. Falls
     * back to the default (injected) repository when absent or unrecognized.
     */
    private function resolveImapRepository(): ImapRepository
    {
        $mailbox = (string) request()->query('mailbox', '');

        $allowed = array_map(
            static fn (DepartmentCourrierEnum $department): string => $department->imapMailbox(),
            DepartmentCourrierEnum::cases(),
        );

        if (in_array($mailbox, $allowed, true)) {
            return new ImapRepository($mailbox);
        }

        return $this->imapRepository;
    }
}
