<?php

declare(strict_types=1);

namespace AcMarche\Agent\Documentation;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Markdown pages stored flat on the knowledge-base disk, laravel/docs style:
 * one standalone {slug}.md file per page, which carries its own menu.
 *
 * Every disk path is built from a slug that passed isValidSlug(), which is
 * what keeps user input from reaching other files on the disk.
 */
final readonly class Documentation
{
    public const string SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const int SLUG_MAX_LENGTH = 100;

    /**
     * Slugs that would collide with page routes.
     *
     * @var array<int, string>
     */
    public const array RESERVED_SLUGS = ['create'];

    public function __construct(private Filesystem $disk) {}

    public function isValidSlug(string $slug): bool
    {
        return mb_strlen($slug) <= self::SLUG_MAX_LENGTH
            && preg_match(self::SLUG_PATTERN, $slug) === 1
            && ! in_array($slug, self::RESERVED_SLUGS, true);
    }

    public function exists(string $slug): bool
    {
        return $this->isValidSlug($slug) && $this->disk->exists($this->path($slug));
    }

    /**
     * @throws FileNotFoundException
     */
    public function get(string $slug): string
    {
        $content = $this->disk->get($this->path($slug));

        if ($content === null) {
            throw new FileNotFoundException("Knowledge base page [{$slug}] does not exist.");
        }

        return $content;
    }

    public function title(string $slug): string
    {
        return $this->titleFromMarkdown($this->get($slug), $slug);
    }

    public function hash(string $slug): string
    {
        return sha1($this->get($slug));
    }

    public function lastModified(string $slug): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp($this->disk->lastModified($this->path($slug)));
    }

    public function put(string $slug, string $markdown): void
    {
        $this->disk->put($this->path($slug), $this->normalize($markdown));
    }

    public function delete(string $slug): void
    {
        $this->disk->delete($this->path($slug));
    }

    /**
     * @return Collection<string, array{slug: string, title: string, updated_at: CarbonImmutable, content: string}>
     */
    public function all(): Collection
    {
        return collect($this->disk->files())
            ->filter(fn (string $file): bool => str_ends_with($file, '.md'))
            ->map(fn (string $file): string => mb_substr($file, 0, -3))
            ->filter(fn (string $slug): bool => $this->isValidSlug($slug))
            ->mapWithKeys(function (string $slug): array {
                $content = $this->get($slug);

                return [$slug => [
                    'slug' => $slug,
                    'title' => $this->titleFromMarkdown($content, $slug),
                    'updated_at' => $this->lastModified($slug),
                    'content' => $content,
                ]];
            })
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * HTML and menu of one page (see PageRenderer). Cached per content hash,
     * so an edit naturally invalidates it.
     *
     * @return array{menu: array<int, array{label: string, anchor: string, children: array<int, array{label: string, anchor: string}>}>, html: string}
     */
    public function render(string $slug): array
    {
        $markdown = $this->get($slug);

        return Cache::remember(
            'knowledge-base:page:v2:'.$slug.':'.sha1($markdown),
            now()->addDay(),
            fn (): array => new PageRenderer()->render($markdown),
        );
    }

    private function path(string $slug): string
    {
        if (! $this->isValidSlug($slug)) {
            throw new InvalidArgumentException("Invalid knowledge base slug [{$slug}].");
        }

        return $slug.'.md';
    }

    private function normalize(string $markdown): string
    {
        return mb_rtrim(str_replace(["\r\n", "\r"], "\n", $markdown))."\n";
    }

    private function titleFromMarkdown(string $markdown, string $slug): string
    {
        if (preg_match('/^# (.+)$/m', $markdown, $matches) === 1) {
            return mb_trim($matches[1]);
        }

        return Str::headline($slug);
    }
}
