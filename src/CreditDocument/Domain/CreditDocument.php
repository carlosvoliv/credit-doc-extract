<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain;

use DateTimeImmutable;
use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\ValueObject\DocumentId;
use Src\CreditDocument\Domain\ValueObject\DocumentType;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/**
 * Aggregate root. A credit document and the structured fields extracted from
 * it. Fields are keyed by name so the invariant "one value per field, highest
 * confidence wins" is enforced here rather than scattered across the app.
 */
final class CreditDocument
{
    /** @var array<string, ExtractedField> */
    private array $fields = [];

    private function __construct(
        public readonly DocumentId $id,
        private DocumentType $type,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public static function start(DocumentType $type): self
    {
        return new self(DocumentId::generate(), $type, new DateTimeImmutable);
    }

    public static function reconstitute(DocumentId $id, DocumentType $type, DateTimeImmutable $createdAt): self
    {
        return new self($id, $type, $createdAt);
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function reclassifyAs(DocumentType $type): void
    {
        $this->type = $type;
    }

    /** Keep only the most confident value when extractors collide on a field. */
    public function recordField(ExtractedField $field): void
    {
        $key = $field->name->value;
        $existing = $this->fields[$key] ?? null;
        if ($existing === null || $field->confidence->value > $existing->confidence->value) {
            $this->fields[$key] = $field;
        }
    }

    public function field(FieldName $name): ?ExtractedField
    {
        return $this->fields[$name->value] ?? null;
    }

    /** @return ExtractedField[] */
    public function fields(): array
    {
        return array_values($this->fields);
    }

    /** Mean confidence across extracted fields (0 when nothing was found). */
    public function overallConfidence(): float
    {
        if ($this->fields === []) {
            return 0.0;
        }
        $sum = array_sum(array_map(fn (ExtractedField $f) => $f->confidence->value, $this->fields));

        return round($sum / count($this->fields), 4);
    }
}
