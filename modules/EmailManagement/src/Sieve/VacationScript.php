<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Sieve;

/**
 * Builds the Sieve script behind an out-of-office reply (RFC 5230).
 *
 * Kept apart from the server connection so the generated script can be asserted on
 * without a ManageSieve server to talk to.
 */
final class VacationScript
{
    public const string SCRIPT_NAME = 'sogo';

    /**
     * @param  array<int, string>  $addresses  the account's own addresses, so that mail sent
     *                                         to an alias is still recognised as its own and
     *                                         answered once
     */
    public static function build(string $subject, string $message, int $days = 1, array $addresses = []): string
    {
        $lines = ['require ["vacation"];', '', 'vacation'];
        $lines[] = '  :days '.max(1, $days);

        if ($addresses !== []) {
            $quoted = array_map(static fn (string $a): string => self::quote($a), array_values($addresses));
            $lines[] = '  :addresses ['.implode(', ', $quoted).']';
        }

        $lines[] = '  :subject '.self::quote(self::singleLine($subject));
        $lines[] = self::multiline($message);

        return implode("\n", $lines)."\n";
    }

    /**
     * Reads back the values of a vacation script, so an existing reply can be edited.
     * Returns null when the script holds no vacation action.
     *
     * @return array{subject: string, message: string, days: int}|null
     */
    public static function parse(string $script): ?array
    {
        $script = str_replace(["\r\n", "\r"], "\n", $script);

        if (preg_match('/\bvacation\b(?<args>.*?)(?:text:[ \t]*\n(?<multi>.*?)\n\.\n|"(?<quoted>(?:[^"\\\\]|\\\\.)*)")\s*;/s', $script, $matches) !== 1) {
            return null;
        }

        $days = preg_match('/:days\s+(\d+)/', $matches['args'], $daysMatch) === 1 ? (int) $daysMatch[1] : 1;
        $subject = preg_match('/:subject\s+"((?:[^"\\\\]|\\\\.)*)"/s', $matches['args'], $subjectMatch) === 1
            ? self::unquote($subjectMatch[1])
            : '';

        $message = ($matches['quoted'] ?? '') !== ''
            ? self::unquote($matches['quoted'])
            : implode("\n", array_map(
                static fn (string $line): string => str_starts_with($line, '..') ? mb_substr($line, 1) : $line,
                explode("\n", $matches['multi']),
            ));

        return ['subject' => $subject, 'message' => $message, 'days' => max(1, $days)];
    }

    /**
     * A Sieve quoted string escapes only the backslash and the double quote, and cannot
     * span lines.
     */
    private static function quote(string $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    private static function unquote(string $value): string
    {
        return (string) preg_replace('/\\\\(.)/s', '$1', $value);
    }

    private static function singleLine(string $value): string
    {
        return mb_trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    /**
     * The multi-line form: everything up to a line holding a single dot. Lines that would
     * start with a dot are stuffed, so a message can contain one without ending the block.
     */
    private static function multiline(string $value): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $value);

        $stuffed = array_map(
            static fn (string $line): string => str_starts_with($line, '.') ? '.'.$line : $line,
            explode("\n", $body),
        );

        return "text:\n".implode("\n", $stuffed)."\n.\n;";
    }
}
