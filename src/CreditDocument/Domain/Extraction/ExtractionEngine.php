<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Extraction;

use Src\CreditDocument\Domain\CreditDocument;

/**
 * Domain service that turns plain text into a populated CreditDocument: it
 * classifies the instrument, then runs every field extractor and lets the
 * aggregate resolve collisions. No I/O, no framework — pure orchestration.
 */
final readonly class ExtractionEngine
{
    /** @param FieldExtractor[] $extractors */
    public function __construct(
        private DocumentClassifier $classifier,
        private array $extractors,
    ) {}

    public function run(string $text): CreditDocument
    {
        $document = CreditDocument::start($this->classifier->classify($text));

        foreach ($this->extractors as $extractor) {
            foreach ($extractor->extract($text) as $field) {
                $document->recordField($field);
            }
        }

        return $document;
    }
}
