<?php

declare(strict_types=1);

namespace AcMarche\Document\Http\Controllers;

use AcMarche\Document\Models\Document;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

final class DocumentController extends Controller
{
    /**
     * Publicly display a single document, with its file previewable inline.
     */
    public function show(Document $document): View
    {
        $document->load('category');

        return view('document::public.show', [
            'document' => $document,
            'fileUrl' => $this->fileUrl($document),
        ]);
    }

    /**
     * The url of the stored file, or null when the document holds none.
     */
    private function fileUrl(Document $document): ?string
    {
        $path = $document->filePathOnDisk();
        if ($path === null) {
            return null;
        }

        $disk = Storage::disk(config('document.storage.disk'));
        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->url($path);
    }
}
