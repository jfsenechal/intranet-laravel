<?php

namespace AcMarche\Courrier\Dto;

final readonly class EmailAttachment
{
    public function __construct(
        public string $filename,
        public ?string $contentType,
        public ?string $extension,
    ) {}
}
