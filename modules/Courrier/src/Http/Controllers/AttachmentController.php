<?php

namespace AcMarche\Courrier\Http\Controllers;

use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Repository\ImapRepository;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentController extends Controller
{
    public function __construct(
        private readonly ImapRepository $imapRepository
    ) {}

    public function show(string $uid, int $index): StreamedResponse|Response
    {
        try {
            $attachment = $this->imapRepository->getAttachment($uid, $index);

            return $this->imapRepository->createAttachmentDownloadResponse($attachment);
        } catch (ImapException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function preview(string $uid, int $index): StreamedResponse|Response
    {
        try {
            $attachment = $this->imapRepository->getAttachment($uid, $index);
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
}
