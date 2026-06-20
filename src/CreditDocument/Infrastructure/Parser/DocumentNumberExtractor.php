<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Extraction\FieldExtractor;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/** Finds the instrument's own number, e.g. "Nota Promissória nº 0001/2025". */
final class DocumentNumberExtractor implements FieldExtractor
{
    public function extract(string $text): array
    {
        if (! preg_match('/n[ºo°]\.?\s*([0-9][\w.\/-]{1,20})/iu', $text, $m)) {
            return [];
        }

        return [new ExtractedField(
            FieldName::DocumentNumber,
            rtrim($m[1], '.'),
            rtrim(trim($m[0]), '.'),
            ConfidenceScore::medium(),
        )];
    }
}
