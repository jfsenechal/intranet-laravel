<?php

declare(strict_types=1);

namespace AcMarche\Agent\Http\Controllers;

use AcMarche\KnowledgeBase\Documentation\ImageStore;
use AcMarche\KnowledgeBase\KnowledgeBaseAccess;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves images embedded in knowledge base pages. Authorization is checked on
 * every request, so a copied image URL is useless without the read permission.
 */
final class ImageController
{
    public function __invoke(string $filename, ImageStore $images): StreamedResponse
    {
        abort_unless(KnowledgeBaseAccess::canRead(auth()->user()), 403);
        abort_unless($images->exists($filename), 404);

        return Storage::disk('knowledge-base')->response($images->path($filename), headers: [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
