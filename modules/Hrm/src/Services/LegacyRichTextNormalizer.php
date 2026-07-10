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
     * Mirror the old display logic (escape, then convert newlines to <br>)
     * so migrated content renders exactly as it did before.
     */
    private function toHtml(string $value): string
    {
        return '<p>'.nl2br(e(mb_trim($value))).'</p>';
    }
}
