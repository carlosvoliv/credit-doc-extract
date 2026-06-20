<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\CreditDocument\Application\ExtractDocument\ExtractDocumentCommand;
use Src\CreditDocument\Application\ExtractDocument\ExtractDocumentHandler;
use Src\CreditDocument\Application\ExtractDocument\ExtractedDocumentDto;
use Src\CreditDocument\Domain\Repository\DocumentRepository;
use Src\CreditDocument\Domain\ValueObject\DocumentId;

/**
 * Interface layer. Thin: validate input, build the command, delegate to the
 * application handler, and shape the HTTP response. No domain logic here.
 */
final class ExtractionController extends Controller
{
    public function store(Request $request, ExtractDocumentHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required_without:file', 'string'],
            'file' => ['required_without:text', 'file', 'mimes:pdf,txt', 'max:10240'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $command = new ExtractDocumentCommand(
                contents: (string) $file->get(),
                mimeType: $file->getClientMimeType(),
            );
        } else {
            $command = new ExtractDocumentCommand(contents: $validated['text']);
        }

        $dto = $handler->handle($command);

        return response()->json($dto->toArray(), 201);
    }

    public function show(string $id, DocumentRepository $repository): JsonResponse
    {
        $document = $repository->find(DocumentId::fromString($id));
        if ($document === null) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        return response()->json(ExtractedDocumentDto::fromDocument($document)->toArray());
    }
}
