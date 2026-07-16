<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The mail domains an address may be issued under.
 *
 * The value carries the leading @, so an address is its local part concatenated with the
 * case value, the way the legacy GestEmail built it.
 */
enum EmailExtensionEnum: string implements HasLabel
{
    case EXTENSION_AC = '@ac.marche.be';
    case EXTENSION_CPAS = '@cpas.marche.be';
    case EXTENSION_MARCHE = '@marche.be';

    /**
     * The extension of an existing address, or null when it sits under an unknown domain.
     */
    public static function fromAddress(?string $mail): ?self
    {
        $position = self::separatorPosition($mail);

        if ($position === null) {
            return null;
        }

        return self::tryFrom(mb_strtolower(mb_substr((string) $mail, $position)));
    }

    /**
     * The part of an address before its domain.
     */
    public static function localPart(?string $mail): ?string
    {
        $position = self::separatorPosition($mail);

        if ($position === null) {
            return $mail;
        }

        return mb_substr((string) $mail, 0, $position);
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }

    /**
     * Offset of the last @, which is where the domain starts.
     */
    private static function separatorPosition(?string $mail): ?int
    {
        if ($mail === null || ! str_contains($mail, '@')) {
            return null;
        }

        $position = mb_strrpos($mail, '@');

        return $position === false ? null : $position;
    }
}
