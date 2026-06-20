<?php

declare(strict_types=1);

namespace Src\CreditDocument\Application\ExtractDocument;

/** Input to the extract-document use case: the raw bytes plus their mime type. */
final readonly class ExtractDocumentCommand
{
    public function __construct(
        public string $contents,
        public string $mimeType = 'text/plain',
    ) {}
}
