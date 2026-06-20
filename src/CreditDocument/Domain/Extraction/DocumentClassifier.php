<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Extraction;

use Src\CreditDocument\Domain\ValueObject\DocumentType;

/** Decides what kind of credit instrument a blob of text represents. */
interface DocumentClassifier
{
    public function classify(string $text): DocumentType;
}
