<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Documentation;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

/**
 * Markdown pages stored flat on the knowledge-base disk, laravel/docs style:
 * one {slug}.md file per page and a documentation.md file describing the
 * sidebar as a nested list ("- ## Section" then "    - [Title](slug)").
 *
 * Every disk path is built from a slug that passed isValidSlug(), which is
 * what keeps user input from reaching other files on the disk.
 */
final readonly class Documentation
{
    public const string NAVIGATION_FILE = 'documentation.md';

    public const string SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const int SLUG_MAX_LENGTH = 100;

    /**
     * Slugs that would collide with the index file or with page routes.
     *
     * @var array<int, string>
     */
    public const array RESERVED_SLUGS = ['documentation', 'create', 'navigation'];

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
     * @return Collection<string, array{slug: string, title: string, section: ?string, updated_at: CarbonImmutable, content: string}>
     */
    public function all(): Collection
    {
        $sections = $this->sectionsBySlug();

        return collect($this->disk->files())
            ->filter(fn (string $file): bool => str_ends_with($file, '.md'))
            ->map(fn (string $file): string => mb_substr($file, 0, -3))
            ->filter(fn (string $slug): bool => $this->isValidSlug($slug))
            ->mapWithKeys(function (string $slug) use ($sections): array {
                $content = $this->get($slug);

                return [$slug => [
                    'slug' => $slug,
                    'title' => $this->titleFromMarkdown($content, $slug),
                    'section' => $sections[$slug] ?? null,
                    'updated_at' => $this->lastModified($slug),
                    'content' => $content,
                ]];
            })
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Sidebar parsed from documentation.md. Entries pointing to an invalid or
     * missing page are dropped, so deleting a page removes its link.
     *
     * @return array<int, array{section: string, pages: array<int, array{slug: string, title: string}>}>
     */
    public function navigation(): array
    {
        $navigation = [];
        $current = null;

        foreach (preg_split('/\R/', $this->navigationSource()) ?: [] as $line) {
            if (preg_match('/^\s*-\s+##\s+(.+?)\s*$/', $line, $matches) === 1) {
                $navigation[] = ['section' => $matches[1], 'pages' => []];
                $current = array_key_last($navigation);

                continue;
            }

            if ($current === null) {
                continue;
            }

            if (preg_match('/^\s*-\s+\[(.+?)\]\(\s*\/?([^)\s]+)\s*\)\s*$/', $line, $matches) === 1 && $this->exists($matches[2])) {
                $navigation[$current]['pages'][] = ['slug' => $matches[2], 'title' => $matches[1]];
            }
        }

        return $navigation;
    }

    public function navigationSource(): string
    {
        if (! $this->disk->exists(self::NAVIGATION_FILE)) {
            return '';
        }

        return (string) $this->disk->get(self::NAVIGATION_FILE);
    }

    public function putNavigation(string $markdown): void
    {
        $this->disk->put(self::NAVIGATION_FILE, $this->normalize($markdown));
    }

    /**
     * Pages that exist on disk but are not listed in documentation.md.
     *
     * @return array<int, string>
     */
    public function unlistedSlugs(): array
    {
        $listed = array_keys($this->sectionsBySlug());

        return $this->all()->keys()->diff($listed)->values()->all();
    }

    /**
     * Raw HTML is escaped and unsafe links are dropped; fenced code blocks are
     * highlighted by Phiki. The result is cached per content hash, so an edit
     * naturally invalidates it.
     */
    public function render(string $slug): string
    {
        $markdown = $this->get($slug);

        return Cache::remember(
            'knowledge-base:html:'.$slug.':'.sha1($markdown),
            now()->addDay(),
            fn (): string => $this->toHtml($markdown),
        );
    }

    public function toHtml(string $markdown): string
    {
        return (string) Str::markdown(
            $markdown,
            ['html_input' => 'escape', 'allow_unsafe_links' => false],
            [new PhikiExtension(['light' => Theme::GithubLight, 'dark' => Theme::GithubDark])],
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

    /**
     * @return array<string, string>
     */
    private function sectionsBySlug(): array
    {
        $sections = [];

        foreach ($this->navigation() as $section) {
            foreach ($section['pages'] as $page) {
                $sections[$page['slug']] ??= $section['section'];
            }
        }

        return $sections;
    }
}
