<?php

declare(strict_types=1);

use AcMarche\Agent\Documentation\PageRenderer;

beforeEach(function (): void {
    $this->renderer = new PageRenderer;
});

it('uses the link list under the title as the page menu, laravel/docs style', function (): void {
    $page = $this->renderer->render(<<<'MD'
        # HTTP Client

        - [Introduction](#introduction)
        - [Making Requests](#making-requests)
            - [Headers](#headers)

        <a name="introduction"></a>
        ## Introduction

        Text.

        <a name="making-requests"></a>
        ## Making Requests

        <a name="headers"></a>
        ### Headers
        MD);

    expect($page['menu'])->toBe([
        ['label' => 'Introduction', 'anchor' => 'introduction', 'children' => []],
        ['label' => 'Making Requests', 'anchor' => 'making-requests', 'children' => [
            ['label' => 'Headers', 'anchor' => 'headers'],
        ]],
    ])
        ->and($page['html'])
        ->toContain('<a id="introduction"></a>')
        ->toContain('<a id="making-requests"></a>')
        ->toContain('<a id="headers"></a>')
        ->not->toContain('&lt;a name')
        ->not->toContain('href="#introduction"');
});

it('builds the menu from the page headings when there is no link list', function (): void {
    $page = $this->renderer->render(<<<'MD'
        # Sauvegarde

        Intro.

        ## Restauration d'une base

        ### Étape `mysql`

        ## Restauration d'une base

        ```bash
        ## not a heading
        ```
        MD);

    expect($page['menu'])->toBe([
        ['label' => "Restauration d'une base", 'anchor' => 'restauration-dune-base', 'children' => [
            ['label' => 'Étape mysql', 'anchor' => 'étape-mysql'],
        ]],
        ['label' => "Restauration d'une base", 'anchor' => 'restauration-dune-base-1', 'children' => []],
    ]);

    foreach (['restauration-dune-base', 'étape-mysql', 'restauration-dune-base-1'] as $anchor) {
        expect($page['html'])->toContain('id="'.$anchor.'"');
    }
});

it('returns an empty menu for a page without sections', function (): void {
    expect($this->renderer->render("# Note\n\nJuste un paragraphe.")['menu'])->toBe([]);
});

it('only turns well-formed named anchors into html', function (): void {
    $html = $this->renderer->toHtml(<<<'MD'
        <a name="ok-anchor"></a>

        <a name="x" onclick="alert(1)"></a>

        <a name="bad&quot;"></a>

        ```html
        <a name="in-code"></a>
        ```
        MD);

    expect($html)->toContain('<a id="ok-anchor"></a>')
        ->toContain('&lt;a name="x" onclick')
        ->not->toContain('<a name="x"')
        ->not->toContain('<a id="x"')
        ->not->toContain('<a id="bad')
        ->not->toContain('<a id="in-code"');
});

it('escapes raw html and drops unsafe links', function (): void {
    $html = $this->renderer->toHtml("<script>alert('x')</script>\n\n[click](javascript:alert(1))");

    expect($html)->toContain('&lt;script&gt;')
        ->not->toContain('<script>')
        ->not->toContain('javascript:');
});

it('highlights fenced code blocks with light and dark themes', function (): void {
    $html = $this->renderer->toHtml("```php\n<?php echo 'kb';\n```");

    expect($html)->toContain('class="phiki')
        ->toContain('--phiki-dark-')
        ->not->toContain("<?php echo 'kb';");
});

it('renders a fenced block without language as escaped code', function (): void {
    $html = $this->renderer->toHtml("```\n<b>raw</b>\n```");

    expect($html)->toContain('&lt;b&gt;raw&lt;/b&gt;')
        ->not->toContain('<b>raw</b>');
});
