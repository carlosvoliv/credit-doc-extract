<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Extraction;

/**
 * Turns raw document bytes (PDF, etc.) into plain text. The domain depends only
 * on this contract; the PDF library lives in Infrastructure.
 */
interface DocumentTextReader
{
    public function supports(string $mimeType): bool;

    public function read(string $contents): string;
}
