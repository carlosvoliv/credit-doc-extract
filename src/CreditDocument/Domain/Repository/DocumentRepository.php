<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\Repository;

use Src\CreditDocument\Domain\CreditDocument;
use Src\CreditDocument\Domain\ValueObject\DocumentId;

interface DocumentRepository
{
    public function save(CreditDocument $document): void;

    public function find(DocumentId $id): ?CreditDocument;
}
