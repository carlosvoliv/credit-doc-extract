<?php

declare(strict_types=1);

namespace Src\CreditDocument\Application\ExtractDocument;

use Src\CreditDocument\Domain\CreditDocument;

/** Flat, serialisable view of an extraction result for the interface layer. */
final readonly class ExtractedDocumentDto
{
    public function __construct(
        public string $id,
        public string $type,
        public string $typeLabel,
        public float $confidence,
        public array $fields,
    ) {}

    public static function fromDocument(CreditDocument $document): self
    {
        return new self(
            id: (string) $document->id,
            type: $document->type()->value,
            typeLabel: $document->type()->label(),
            confidence: $document->overallConfidence(),
            fields: array_map(fn ($f) => $f->toArray(), $document->fields()),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->typeLabel,
            'confidence' => $this->confidence,
            'fields' => $this->fields,
        ];
    }
}
