<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Persistence;

use App\Models\ExtractedDocumentRecord;
use DateTimeImmutable;
use DateTimeInterface;
use Src\CreditDocument\Domain\CreditDocument;
use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Repository\DocumentRepository;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\DocumentId;
use Src\CreditDocument\Domain\ValueObject\DocumentType;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/**
 * Data-mapper repository. The domain aggregate never knows Eloquent exists; this
 * class flattens it on save and rebuilds it (with all value objects intact) on
 * load.
 */
final class EloquentDocumentRepository implements DocumentRepository
{
    public function save(CreditDocument $document): void
    {
        ExtractedDocumentRecord::query()->updateOrCreate(
            ['id' => (string) $document->id],
            [
                'type' => $document->type()->value,
                'confidence' => $document->overallConfidence(),
                'fields' => array_map(fn (ExtractedField $f) => $f->toArray(), $document->fields()),
            ],
        );
    }

    public function find(DocumentId $id): ?CreditDocument
    {
        $record = ExtractedDocumentRecord::query()->find((string) $id);
        if ($record === null) {
            return null;
        }

        $createdAt = $record->created_at instanceof DateTimeInterface
            ? DateTimeImmutable::createFromInterface($record->created_at)
            : new DateTimeImmutable;

        $document = CreditDocument::reconstitute(
            DocumentId::fromString($record->id),
            DocumentType::from($record->type),
            $createdAt,
        );

        foreach ($record->fields as $field) {
            $document->recordField(new ExtractedField(
                FieldName::from($field['name']),
                $field['value'],
                $field['raw'],
                new ConfidenceScore((float) $field['confidence']),
            ));
        }

        return $document;
    }
}
