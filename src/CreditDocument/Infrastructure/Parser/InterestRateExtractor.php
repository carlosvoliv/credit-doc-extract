<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Extraction\FieldExtractor;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/** Finds an interest rate like "juros de 1,89% a.m." → "1.89". */
final class InterestRateExtractor implements FieldExtractor
{
    public function extract(string $text): array
    {
        if (! preg_match('/(?:juros|taxa)(?:\s+de)?\D{0,15}(\d{1,3}(?:[.,]\d{1,4})?)\s*%/iu', $text, $m)) {
            return [];
        }

        $value = str_replace(',', '.', $m[1]);

        return [new ExtractedField(
            FieldName::InterestRate,
            $value,
            trim($m[0]),
            ConfidenceScore::high(),
        )];
    }
}
