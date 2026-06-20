<?php

declare(strict_types=1);

namespace Src\CreditDocument\Application\ExtractDocument;

use RuntimeException;
use Src\CreditDocument\Domain\Extraction\DocumentTextReader;
use Src\CreditDocument\Domain\Extraction\ExtractionEngine;
use Src\CreditDocument\Domain\Repository\DocumentRepository;

/**
 * Use-case orchestrator: pick a reader for the input's mime type, decode it to
 * text, run the extraction engine, persist the aggregate, and return a DTO.
 * This is the only place that ties domain pieces to a transactional boundary.
 */
final readonly class ExtractDocumentHandler
{
    /** @param iterable<DocumentTextReader> $readers */
    public function __construct(
        private ExtractionEngine $engine,
        private DocumentRepository $repository,
        private iterable $readers,
    ) {}

    public function handle(ExtractDocumentCommand $command): ExtractedDocumentDto
    {
        $text = $this->decode($command);

        $document = $this->engine->run($text);
        $this->repository->save($document);

        return ExtractedDocumentDto::fromDocument($document);
    }

    private function decode(ExtractDocumentCommand $command): string
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($command->mimeType)) {
                return $reader->read($command->contents);
            }
        }

        throw new RuntimeException("No reader registered for mime type: {$command->mimeType}");
    }
}
