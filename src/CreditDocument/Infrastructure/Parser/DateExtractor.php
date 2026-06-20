<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Extraction\FieldExtractor;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;

/**
 * Pulls issue and due dates. Labels ("emissão", "vencimento") disambiguate
 * which date is which; dates are normalised to ISO (yyyy-mm-dd).
 */
final class DateExtractor implements FieldExtractor
{
    private const DATE = '(\d{2})\/(\d{2})\/(\d{4})';

    public function extract(string $text): array
    {
        $fields = [];

        if (preg_match('/(?:emiss[ãa]o|emitid[ao]\s+em|data\s+de\s+emiss[ãa]o)\D{0,15}'.self::DATE.'/iu', $text, $m)) {
            $fields[] = $this->field(FieldName::IssueDate, $m);
        }

        if (preg_match('/(?:vencimento|vence\s+em|data\s+de\s+vencimento)\D{0,15}'.self::DATE.'/iu', $text, $m)) {
            $fields[] = $this->field(FieldName::DueDate, $m);
        }

        return $fields;
    }

    private function field(FieldName $name, array $m): ExtractedField
    {
        $iso = "{$m[3]}-{$m[2]}-{$m[1]}";

        return new ExtractedField($name, $iso, trim($m[0]), ConfidenceScore::high());
    }
}
