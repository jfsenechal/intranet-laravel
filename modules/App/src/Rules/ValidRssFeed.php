<?php

declare(strict_types=1);

namespace AcMarche\App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ValidRssFeed implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Le flux RSS n\'est pas valide.');

            return;
        }

        try {
            $response = Http::timeout(5)->get($value);
        } catch (Throwable) {
            $fail('Impossible de joindre l\'URL fournie.');

            return;
        }

        if (! $response->successful()) {
            $fail('L\'URL a renvoyé une réponse invalide.');

            return;
        }

        $xml = @simplexml_load_string($response->body());

        if ($xml === false) {
            $fail('Le contenu n\'est pas un flux RSS/Atom valide.');

            return;
        }

        $hasRssItems = isset($xml->channel->item);
        $hasAtomEntries = isset($xml->entry);

        if (! $hasRssItems && ! $hasAtomEntries) {
            $fail('Le flux ne contient pas d\'éléments RSS ou Atom.');
        }
    }
}
