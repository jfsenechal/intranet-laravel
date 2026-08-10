<?php

declare(strict_types=1);

use AcMarche\Hrm\Services\LegacyRichTextNormalizer;

beforeEach(function (): void {
    $this->normalizer = new LegacyRichTextNormalizer;
});

it('wraps legacy plain text in a paragraph and converts newlines to <br>', function (): void {
    expect($this->normalizer->normalize("Ligne 1\nLigne 2"))
        ->toBe("<p>Ligne 1<br />\nLigne 2</p>");
});

it('escapes html-special characters in legacy plain text', function (): void {
    expect($this->normalizer->normalize('Salaire < 2000 & primes'))
        ->toBe('<p>Salaire &lt; 2000 &amp; primes</p>');
});

it('leaves content that already contains html untouched', function (): void {
    $html = '<p>Déjà du <strong>HTML</strong></p>';

    expect($this->normalizer->normalize($html))->toBe($html);
});

it('leaves html with a lone <br> tag untouched', function (): void {
    $html = 'Ligne 1<br>Ligne 2';

    expect($this->normalizer->normalize($html))->toBe($html);
});

it('returns null and empty values unchanged', function (?string $value): void {
    expect($this->normalizer->normalize($value))->toBe($value);
})->with([
    'null' => [null],
    'empty string' => [''],
    'only whitespace' => ['   '],
]);

it('trims surrounding whitespace before wrapping', function (): void {
    expect($this->normalizer->normalize("  Bonjour  \n"))
        ->toBe('<p>Bonjour</p>');
});

describe('normalizeForEditor', function (): void {
    it('wraps inline html so the editor can start a new line', function (): void {
        expect($this->normalizer->normalizeForEditor('Ligne 1<br>Ligne 2'))
            ->toBe('<p>Ligne 1<br>Ligne 2</p>');
    });

    it('wraps html starting with an inline formatting tag', function (): void {
        expect($this->normalizer->normalizeForEditor('<strong>Important</strong> à relire'))
            ->toBe('<p><strong>Important</strong> à relire</p>');
    });

    it('leaves content already starting with a block element untouched', function (string $html): void {
        expect($this->normalizer->normalizeForEditor($html))->toBe($html);
    })->with([
        'paragraph' => ['<p>Déjà du <strong>HTML</strong></p>'],
        'leading whitespace' => ["\n  <p>Indenté</p>"],
        'bullet list' => ['<ul><li>Un</li><li>Deux</li></ul>'],
        'heading' => ['<h2>Titre</h2><p>Suite</p>'],
        'blockquote' => ['<blockquote><p>Citation</p></blockquote>'],
        'table' => ['<table><tr><td>A</td></tr></table>'],
        'horizontal rule' => ['<hr><p>Suite</p>'],
    ]);

    it('wraps legacy plain text exactly like normalize()', function (): void {
        expect($this->normalizer->normalizeForEditor("Ligne 1\nLigne 2"))
            ->toBe("<p>Ligne 1<br />\nLigne 2</p>");
    });

    it('returns null and empty values unchanged', function (?string $value): void {
        expect($this->normalizer->normalizeForEditor($value))->toBe($value);
    })->with([
        'null' => [null],
        'empty string' => [''],
        'only whitespace' => ['   '],
    ]);
});
