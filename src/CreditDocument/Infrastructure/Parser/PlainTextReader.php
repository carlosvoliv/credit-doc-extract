<?php

declare(strict_types=1);

namespace Src\CreditDocument\Infrastructure\Parser;

use Src\CreditDocument\Domain\Extraction\DocumentTextReader;

final class PlainTextReader implements DocumentTextReader
{
    public function supports(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'text/');
    }

    public function read(string $contents): string
    {
        return $contents;
    }
}
