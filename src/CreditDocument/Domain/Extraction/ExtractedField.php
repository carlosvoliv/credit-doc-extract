<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Extraction;

use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/**
 * One piece of structured data pulled from a document: which field it is, the
 * normalised value, the verbatim text it came from, and how sure we are.
 */
final readonly class ExtractedField
{
    public function __construct(
        public FieldName $name,
        public string $value,
        public string $raw,
        public ConfidenceScore $confidence,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name->value,
            'label' => $this->name->label(),
            'value' => $this->value,
            'raw' => $this->raw,
            'confidence' => round($this->confidence->value, 2),
        ];
    }
}
