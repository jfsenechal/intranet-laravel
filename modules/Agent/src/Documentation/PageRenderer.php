<?php

declare(strict_types=1);

namespace AcMarche\Agent\Documentation;

use Illuminate\Support\Str;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Normalizer\SlugNormalizer;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

/**
 * Turns one standalone Markdown page into HTML plus its own menu.
 *
 * Pages follow the laravel/docs format: an H1, then a list of "(#anchor)"
 * links that is the page menu, then sections preceded by
 * <a name="anchor"></a>. When that list is absent the menu is built from the
 * page's ## and ### headings instead.
 *
 * @phpstan-type MenuItem array{label: string, anchor: string, children: array<int, array{label: string, anchor: string}>}
 */
final class PageRenderer
{
    private const string ANCHOR_LINE = '/^\s*<a\s+name="([A-Za-z0-9_-]+)"\s*>\s*<\/a>\s*$/';

    private const string MENU_LINE = '/^(\s*)[-*]\s+\[(.+?)\]\(#([A-Za-z0-9_-]+)\)\s*$/';

    private const string ANCHOR_TOKEN = '@@kb-anchor:%s@@';

    /**
     * @return array{menu: array<int, MenuItem>, html: string}
     */
    public function render(string $markdown): array
    {
        $lines = preg_split('/\R/', $markdown) ?: [];
        $outsideCode = $this->linesOutsideCode($lines);

        [$menu, $lines] = $this->extractMenu($lines, $outsideCode);

        if ($menu !== []) {
            return ['menu' => $menu, 'html' => $this->toHtml(implode("\n", $lines))];
        }

        return [
            'menu' => $this->menuFromHeadings($lines, $this->linesOutsideCode($lines)),
            'html' => $this->toHtml(implode("\n", $lines), withHeadingIds: true),
        ];
    }

    /**
     * Raw HTML is escaped and unsafe links are dropped. The only HTML a page
     * can produce on purpose is a named anchor, rebuilt here from a token so
     * the anchor name never reaches the output unvalidated.
     *
     * Heading ids are only added when the menu is built from the headings;
     * laravel/docs pages already carry their own anchors.
     */
    public function toHtml(string $markdown, bool $withHeadingIds = false): string
    {
        $lines = preg_split('/\R/', $markdown) ?: [];
        $outsideCode = $this->linesOutsideCode($lines);

        foreach ($lines as $index => $line) {
            if ($outsideCode[$index] && preg_match(self::ANCHOR_LINE, $line, $matches) === 1) {
                $lines[$index] = "\n".sprintf(self::ANCHOR_TOKEN, $matches[1])."\n";
            }
        }

        $options = ['html_input' => 'escape', 'allow_unsafe_links' => false];
        $extensions = [new PhikiExtension(['light' => Theme::GithubLight, 'dark' => Theme::GithubDark])];

        if ($withHeadingIds) {
            $options['heading_permalink'] = [
                'insert' => 'none',
                'apply_id_to_heading' => true,
                'id_prefix' => '',
                'fragment_prefix' => '',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
            ];
            $extensions[] = new HeadingPermalinkExtension;
        }

        $html = (string) Str::markdown(implode("\n", $lines), $options, $extensions);

        return (string) preg_replace(
            '/<p>'.sprintf(preg_quote(self::ANCHOR_TOKEN, '/'), '([A-Za-z0-9_-]+)').'<\/p>/',
            '<a id="$1"></a>',
            $html,
        );
    }

    /**
     * The menu is the first link list directly under the H1, as in laravel/docs.
     * It is removed from the body because the page view shows it as the menu.
     *
     * @param  array<int, string>  $lines
     * @param  array<int, bool>  $outsideCode
     * @return array{0: array<int, MenuItem>, 1: array<int, string>}
     */
    private function extractMenu(array $lines, array $outsideCode): array
    {
        $titleIndex = null;
        foreach ($lines as $index => $line) {
            if ($outsideCode[$index] && preg_match('/^# \S/', $line) === 1) {
                $titleIndex = $index;

                break;
            }
        }

        if ($titleIndex === null) {
            return [[], $lines];
        }

        $menu = [];
        $menuIndexes = [];

        for ($index = $titleIndex + 1, $count = count($lines); $index < $count; $index++) {
            $line = $lines[$index];

            if (mb_trim($line) === '') {
                if ($menu !== []) {
                    break;
                }

                continue;
            }

            if (preg_match(self::MENU_LINE, $line, $matches) !== 1) {
                break;
            }

            $item = ['label' => $matches[2], 'anchor' => $matches[3]];

            if ($matches[1] !== '' && $menu !== []) {
                $menu[array_key_last($menu)]['children'][] = $item;
            } else {
                $menu[] = [...$item, 'children' => []];
            }

            $menuIndexes[] = $index;
        }

        if ($menu === []) {
            return [[], $lines];
        }

        return [$menu, array_values(array_diff_key($lines, array_flip($menuIndexes)))];
    }

    /**
     * Fallback menu: ## become items and ### their children. Anchors use the
     * same slug normaliser as the heading ids CommonMark generates.
     *
     * @param  array<int, string>  $lines
     * @param  array<int, bool>  $outsideCode
     * @return array<int, MenuItem>
     */
    private function menuFromHeadings(array $lines, array $outsideCode): array
    {
        $normalizer = new SlugNormalizer;
        $used = [];
        $menu = [];

        foreach ($lines as $index => $line) {
            if (! $outsideCode[$index] || preg_match('/^(#{2,3})\s+(.+?)\s*#*\s*$/', $line, $matches) !== 1) {
                continue;
            }

            $label = mb_trim((string) preg_replace('/[`*]/', '', $matches[2]));
            $anchor = $normalizer->normalize($label);
            $base = $anchor;
            $suffix = 1;
            while (isset($used[$anchor])) {
                $anchor = $base.'-'.$suffix++;
            }
            $used[$anchor] = true;

            if ($matches[1] === '###' && $menu !== []) {
                $menu[array_key_last($menu)]['children'][] = ['label' => $label, 'anchor' => $anchor];
            } else {
                $menu[] = ['label' => $label, 'anchor' => $anchor, 'children' => []];
            }
        }

        return $menu;
    }

    /**
     * Flags each line as outside (true) or inside (false) a fenced code block,
     * so headings, anchors and links shown as code are left untouched.
     *
     * @param  array<int, string>  $lines
     * @return array<int, bool>
     */
    private function linesOutsideCode(array $lines): array
    {
        $flags = [];
        $fence = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^\s{0,3}(`{3,}|~{3,})/', $line, $matches) === 1) {
                if ($fence === null) {
                    $fence = $matches[1][0];
                    $flags[$index] = false;

                    continue;
                }

                if ($matches[1][0] === $fence) {
                    $fence = null;
                    $flags[$index] = false;

                    continue;
                }
            }

            $flags[$index] = $fence === null;
        }

        return $flags;
    }
}
