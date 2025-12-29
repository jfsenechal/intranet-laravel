<?php

namespace AcMarche\Courrier\Dto;

final readonly class EmailAttachment
{
    public function __construct(
        public string $filename,
        public ?string $contentType,
        public ?string $extension,
    ) {}

    /**
     * @return array{filename: string, content_type: ?string, extension: ?string}
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'content_type' => $this->contentType,
            'extension' => $this->extension,
        ];
    }
}
