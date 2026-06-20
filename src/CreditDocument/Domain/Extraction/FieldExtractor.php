<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Extraction;

/**
 * Strategy contract: each extractor knows how to find ONE kind of field in raw
 * document text. The engine composes many of them. Keeping this an interface is
 * what lets the regex extractors be swapped for ML/LLM-backed ones later without
 * touching the application layer.
 */
interface FieldExtractor
{
    /** @return ExtractedField[] zero or more matches found in the text. */
    public function extract(string $text): array;
}
