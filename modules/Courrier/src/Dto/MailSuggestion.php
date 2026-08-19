<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

/**
 * Fields an AI analysis of an incoming mail attachment suggests for the form.
 */
final readonly class MailSuggestion
{
    /**
     * @param  array<int, string>  $services  service codes read off the reception stamp, still
     *                                        unresolved: matching them to rows is the caller's job
     */
    public function __construct(
        public string $referenceNumber,
        public array $services,
        public string $sender,
        public string $description,
        public bool $isRegistered,
        public bool $hasAcknowledgment,
    ) {}

    /**
     * @param  array{reference_number?: mixed, services?: mixed, sender?: mixed, description?: mixed, is_registered?: mixed, has_acknowledgment?: mixed}  $structured
     */
    public static function fromStructuredResponse(array $structured): self
    {
        return new self(
            mb_trim((string) ($structured['reference_number'] ?? '')),
            self::cleanServices($structured['services'] ?? []),
            mb_trim((string) ($structured['sender'] ?? '')),
            mb_trim((string) ($structured['description'] ?? '')),
            (bool) ($structured['is_registered'] ?? false),
            (bool) ($structured['has_acknowledgment'] ?? false),
        );
    }

    /**
     * @return array{reference_number: string, services: array<int, string>, sender: string, description: string, is_registered: bool, has_acknowledgment: bool}
     */
    public function toArray(): array
    {
        return [
            'reference_number' => $this->referenceNumber,
            'services' => $this->services,
            'sender' => $this->sender,
            'description' => $this->description,
            'is_registered' => $this->isRegistered,
            'has_acknowledgment' => $this->hasAcknowledgment,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function cleanServices(mixed $services): array
    {
        if (! is_array($services)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(fn (mixed $service): string => mb_trim((string) $service), $services),
            fn (string $service): bool => $service !== '',
        )));
    }
}
