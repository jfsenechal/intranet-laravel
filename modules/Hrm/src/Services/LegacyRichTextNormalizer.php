<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Services;

/**
 * Converts legacy plain-text values (former textareas displayed with nl2br())
 * into HTML for RichEditor columns, while leaving values that already contain
 * HTML untouched so they are never double-encoded.
 */
final class LegacyRichTextNormalizer
{
    /**
     * @return string|null The HTML representation, or the original value when it
     *                     is already HTML, empty, or null.
     */
    public function normalize(?string $value): ?string
    {
        if ($value === null || mb_trim($value) === '') {
            return $value;
        }

        if ($this->looksLikeHtml($value)) {
            return $value;
        }

        return $this->toHtml($value);
    }

    /**
     * Prepare a stored value for a RichEditor field.
     *
     * TipTap needs the content to start with a block element, otherwise the
     * whole value is loaded as a single inline node and the user cannot create
     * a new line. Legacy values saved as bare inline HTML (`Ligne 1<br>Ligne 2`)
     * are therefore wrapped in a paragraph.
     *
     * @return string|null The HTML ready for the editor, or the original value
     *                     when it is empty or null.
     */
    public function normalizeForEditor(?string $value): ?string
    {
        $normalized = $this->normalize($value);

        if ($normalized === null || mb_trim($normalized) === '') {
            return $normalized;
        }

        if ($this->startsWithBlockElement($normalized)) {
            return $normalized;
        }

        return '<p>'.$normalized.'</p>';
    }

    /**
     * Detect content already produced by the RichEditor (real HTML tags).
     */
    public function looksLikeHtml(string $value): bool
    {
        return (bool) preg_match(
            '/<\/?(p|br|ul|ol|li|strong|em|b|i|u|a|h[1-6]|blockquote|div|span|table|tr|td|th|img|pre|code)\b[^>]*>/i',
            $value,
        );
    }

    /**
     * Detect a leading block-level tag, the only shape TipTap treats as a
     * standalone block the user can type after.
     */
    public function startsWithBlockElement(string $value): bool
    {
        return (bool) preg_match(
            '/^\s*<(p|ul|ol|h[1-6]|blockquote|div|pre|table|figure|hr)\b/i',
            $value,
        );
    }

    /**
     * Mirror the old display logic (escape, then convert newlines to <br>)
     * so migrated content renders exactly as it did before.
     */
    private function toHtml(string $value): string
    {
        return '<p>'.nl2br(e(mb_trim($value))).'</p>';
    }
}
