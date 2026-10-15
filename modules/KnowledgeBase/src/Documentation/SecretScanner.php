<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Documentation;

/**
 * Flags lines that look like credentials. Secrets belong in the password vault,
 * never in the knowledge base; the scan only warns, it does not block saving.
 */
final class SecretScanner
{
    /**
     * @var array<string, string>
     */
    private const array RULES = [
        'private-key' => '/-----BEGIN [A-Z ]*PRIVATE KEY-----/',
        'password' => '/\b(pass(word)?|pwd|mot de passe|mdp)\s*[:=]\s*\S{4,}/iu',
        'token' => '/\b(api[_-]?key|secret|token)\s*[:=]\s*[A-Za-z0-9_\-\/+]{12,}/i',
        'aws-key' => '/\bAKIA[0-9A-Z]{16}\b/',
    ];

    /**
     * @return array<int, array{line: int, rule: string}>
     */
    public function findings(string $markdown): array
    {
        $findings = [];

        foreach (preg_split('/\R/', $markdown) ?: [] as $index => $line) {
            foreach (self::RULES as $rule => $pattern) {
                if (preg_match($pattern, $line) === 1) {
                    $findings[] = ['line' => $index + 1, 'rule' => $rule];
                }
            }
        }

        return $findings;
    }
}
