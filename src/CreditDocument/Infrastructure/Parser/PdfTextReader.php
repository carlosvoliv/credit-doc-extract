<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Smalot\PdfParser\Parser as PdfParser;
use Src\CreditDocument\Domain\Extraction\DocumentTextReader;

/** Adapter over smalot/pdfparser — the only place that knows about the PDF lib. */
final readonly class PdfTextReader implements DocumentTextReader
{
    public function __construct(private PdfParser $parser = new PdfParser) {}

    public function supports(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    public function read(string $contents): string
    {
        return $this->parser->parseContent($contents)->getText();
    }
}
