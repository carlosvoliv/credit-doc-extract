<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\ExtractedField;
use Src\CreditDocument\Domain\Extraction\FieldExtractor;
use Src\CreditDocument\Domain\ValueObject\ConfidenceScore;
use Src\CreditDocument\Domain\ValueObject\FieldName;
use Src\CreditDocument\Domain\ValueObject\TaxId;

/**
 * Finds taxpayer ids (CPF/CNPJ), validates their check digits via the TaxId
 * value object (invalid numbers are silently skipped), and assigns each to the
 * debtor or creditor role using the words around it.
 */
final class TaxIdExtractor implements FieldExtractor
{
    private const PATTERN = '/\d{3}\.?\d{3}\.?\d{3}-?\d{2}|\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}/u';

    public function extract(string $text): array
    {
        if (! preg_match_all(self::PATTERN, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $fields = [];
        foreach ($matches[0] as [$raw, $offset]) {
            $taxId = TaxId::tryParse($raw);
            if ($taxId === null) {
                continue;
            }

            $context = mb_strtolower(substr($text, max(0, $offset - 60), 60));
            $role = $this->roleFromContext($context);
            if ($role === null) {
                continue;
            }

            $fields[] = new ExtractedField($role, $taxId->masked(), trim($raw), ConfidenceScore::high());
        }

        return $fields;
    }

    private function roleFromContext(string $context): ?FieldName
    {
        if (preg_match('/credor|benefici[áa]rio|cession[áa]rio/u', $context)) {
            return FieldName::CreditorTaxId;
        }
        if (preg_match('/devedor|emitente|sacado|tomador|cedente/u', $context)) {
            return FieldName::DebtorTaxId;
        }

        return null;
    }
}
