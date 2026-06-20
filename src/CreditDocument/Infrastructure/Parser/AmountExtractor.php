<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Extraction\FieldExtractor;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;
use Src\CreditDocument\Domain\ValueObject\Money;

/**
 * Finds the principal amount. A label-anchored hit ("valor principal: R$ …")
 * is high confidence; a bare currency amount anywhere in the text is a medium-
 * confidence fallback.
 */
final class AmountExtractor implements FieldExtractor
{
    public function extract(string $text): array
    {
        if (preg_match('/(?:valor\s+principal|valor\s+do\s+cr[ée]dito|principal)\D{0,20}(R\$\s?[\d.,]+)/iu', $text, $m)) {
            return $this->field($m[1], ConfidenceScore::high());
        }

        if (preg_match('/R\$\s?[\d.,]+/u', $text, $m)) {
            return $this->field($m[0], ConfidenceScore::medium());
        }

        return [];
    }

    private function field(string $raw, ConfidenceScore $confidence): array
    {
        $money = Money::parse($raw);
        if ($money === null) {
            return [];
        }

        return [new ExtractedField(FieldName::PrincipalAmount, $money->format(), trim($raw), $confidence)];
    }
}
