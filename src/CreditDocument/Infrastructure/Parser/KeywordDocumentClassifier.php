<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\DocumentClassifier;
use Src\CreditDocument\Domain\ValueObject\DocumentType;

/**
 * Lightweight keyword classifier. Scores each instrument type by how many of
 * its signature terms appear, and returns the best match (Unknown on a tie at
 * zero). A deliberately simple, explainable baseline — swappable for a model.
 */
final class KeywordDocumentClassifier implements DocumentClassifier
{
    private const SIGNATURES = [
        DocumentType::PromissoryNote->value => ['nota promissória', 'promissória', 'pagarei', 'pagaremos'],
        DocumentType::LoanContract->value => ['contrato de empréstimo', 'mútuo', 'cédula de crédito', 'financiamento'],
        DocumentType::CreditAssignment->value => ['cessão de crédito', 'cedente', 'cessionário', 'termo de cessão'],
        DocumentType::Invoice->value => ['nota fiscal', 'duplicata', 'fatura'],
    ];

    public function classify(string $text): DocumentType
    {
        $haystack = mb_strtolower($text);

        $best = DocumentType::Unknown;
        $bestScore = 0;
        foreach (self::SIGNATURES as $type => $terms) {
            $score = 0;
            foreach ($terms as $term) {
                $score += substr_count($haystack, $term);
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = DocumentType::from($type);
            }
        }

        return $best;
    }
}
