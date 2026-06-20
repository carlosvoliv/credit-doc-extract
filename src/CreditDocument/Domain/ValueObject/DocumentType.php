<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

/**
 * The kinds of credit instrument this engine understands. Generic, public
 * instrument categories — no client- or product-specific document types.
 */
enum DocumentType: string
{
    case PromissoryNote = 'promissory_note';
    case LoanContract = 'loan_contract';
    case CreditAssignment = 'credit_assignment';
    case Invoice = 'invoice';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::PromissoryNote => 'Promissory Note',
            self::LoanContract => 'Loan Contract',
            self::CreditAssignment => 'Credit Assignment',
            self::Invoice => 'Invoice',
            self::Unknown => 'Unknown',
        };
    }
}
