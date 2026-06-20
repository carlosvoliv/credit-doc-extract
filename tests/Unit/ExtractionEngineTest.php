<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Src\CreditDocument\Domain\Extraction\ExtractionEngine;
use Src\CreditDocument\Domain\ValueObject\DocumentType;
use Src\CreditDocument\Domain\ValueObject\FieldName;
use Src\CreditDocument\Infrastructure\Parser\AmountExtractor;
use Src\CreditDocument\Infrastructure\Parser\DateExtractor;
use Src\CreditDocument\Infrastructure\Parser\DocumentNumberExtractor;
use Src\CreditDocument\Infrastructure\Parser\InterestRateExtractor;
use Src\CreditDocument\Infrastructure\Parser\KeywordDocumentClassifier;
use Src\CreditDocument\Infrastructure\Parser\TaxIdExtractor;

final class ExtractionEngineTest extends TestCase
{
    private const SAMPLE = <<<'TXT'
        NOTA PROMISSÓRIA nº 0001/2025

        Aos quinze dias do mês de janeiro, pagarei por esta única via de NOTA
        PROMISSÓRIA a quantia de valor principal de R$ 50.000,00 (cinquenta mil
        reais), com juros de 1,89% ao mês.

        Data de emissão: 15/01/2025
        Data de vencimento: 15/01/2026

        Emitente (devedor): João da Silva, CPF 529.982.247-25
        Credor (beneficiário): Acme Fomento Mercantil, CNPJ 11.222.333/0001-81
        TXT;

    private function engine(): ExtractionEngine
    {
        return new ExtractionEngine(
            new KeywordDocumentClassifier,
            [
                new AmountExtractor,
                new InterestRateExtractor,
                new DateExtractor,
                new TaxIdExtractor,
                new DocumentNumberExtractor,
            ],
        );
    }

    public function test_classifies_a_promissory_note(): void
    {
        $document = $this->engine()->run(self::SAMPLE);

        $this->assertSame(DocumentType::PromissoryNote, $document->type());
    }

    public function test_extracts_every_known_field(): void
    {
        $document = $this->engine()->run(self::SAMPLE);

        $this->assertSame('R$ 50.000,00', $document->field(FieldName::PrincipalAmount)?->value);
        $this->assertSame('1.89', $document->field(FieldName::InterestRate)?->value);
        $this->assertSame('2025-01-15', $document->field(FieldName::IssueDate)?->value);
        $this->assertSame('2026-01-15', $document->field(FieldName::DueDate)?->value);
        $this->assertSame('529.***.***-25', $document->field(FieldName::DebtorTaxId)?->value);
        $this->assertSame('11.***.***/****-81', $document->field(FieldName::CreditorTaxId)?->value);
        $this->assertSame('0001/2025', $document->field(FieldName::DocumentNumber)?->value);
    }

    public function test_overall_confidence_is_within_range(): void
    {
        $document = $this->engine()->run(self::SAMPLE);

        $this->assertGreaterThan(0.0, $document->overallConfidence());
        $this->assertLessThanOrEqual(1.0, $document->overallConfidence());
    }
}
