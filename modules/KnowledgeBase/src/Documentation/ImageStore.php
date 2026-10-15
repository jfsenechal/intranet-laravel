<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Documentation;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Images uploaded from the Markdown editor. They stay on the private disk and
 * are only reachable through the knowledge-base.image route, which re-checks
 * the read permission on every request.
 */
final readonly class ImageStore
{
    public const string DIRECTORY = 'images';

    private const string FILENAME_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(png|jpg|gif|webp)$/';

    private const array EXTENSIONS = ['png', 'jpg', 'gif', 'webp'];

    public function __construct(private Filesystem $disk) {}

    public function isValidFilename(string $filename): bool
    {
        return preg_match(self::FILENAME_PATTERN, $filename) === 1;
    }

    public function store(UploadedFile $file): string
    {
        $extension = $file->guessExtension();
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if (! in_array($extension, self::EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'content' => 'Seules les images PNG, JPG, GIF et WebP sont acceptées.',
            ]);
        }

        $filename = Str::uuid()->toString().'.'.$extension;

        $this->disk->putFileAs(self::DIRECTORY, $file, $filename);

        return $filename;
    }

    public function url(string $filename): string
    {
        return route('knowledge-base.image', ['filename' => $filename], absolute: false);
    }

    public function exists(string $filename): bool
    {
        return $this->isValidFilename($filename) && $this->disk->exists($this->path($filename));
    }

    public function path(string $filename): string
    {
        return self::DIRECTORY.'/'.$filename;
    }
}
