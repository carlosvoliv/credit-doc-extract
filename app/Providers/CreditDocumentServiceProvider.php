<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\CreditDocument\Application\ExtractDocument\ExtractDocumentHandler;
use Src\CreditDocument\Domain\Extraction\DocumentClassifier;
use Src\CreditDocument\Domain\Extraction\ExtractionEngine;
use Src\CreditDocument\Domain\Repository\DocumentRepository;
use Src\CreditDocument\Infrastructure\Parser\AmountExtractor;
use Src\CreditDocument\Infrastructure\Parser\DateExtractor;
use Src\CreditDocument\Infrastructure\Parser\DocumentNumberExtractor;
use Src\CreditDocument\Infrastructure\Parser\InterestRateExtractor;
use Src\CreditDocument\Infrastructure\Parser\KeywordDocumentClassifier;
use Src\CreditDocument\Infrastructure\Parser\PdfTextReader;
use Src\CreditDocument\Infrastructure\Parser\PlainTextReader;
use Src\CreditDocument\Infrastructure\Parser\TaxIdExtractor;
use Src\CreditDocument\Infrastructure\Persistence\EloquentDocumentRepository;

/**
 * Composition root for the CreditDocument context: binds domain contracts to
 * their infrastructure implementations and assembles the extractor/reader sets.
 * This is the one Laravel-aware file that knows the full wiring.
 */
final class CreditDocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DocumentClassifier::class, KeywordDocumentClassifier::class);
        $this->app->bind(DocumentRepository::class, EloquentDocumentRepository::class);

        $this->app->singleton(ExtractionEngine::class, fn ($app) => new ExtractionEngine(
            $app->make(DocumentClassifier::class),
            [
                new AmountExtractor,
                new InterestRateExtractor,
                new DateExtractor,
                new TaxIdExtractor,
                new DocumentNumberExtractor,
            ],
        ));

        $this->app->singleton(ExtractDocumentHandler::class, fn ($app) => new ExtractDocumentHandler(
            $app->make(ExtractionEngine::class),
            $app->make(DocumentRepository::class),
            [new PlainTextReader, new PdfTextReader],
        ));
    }
}
