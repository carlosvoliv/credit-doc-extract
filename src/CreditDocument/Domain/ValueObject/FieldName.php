<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

/** The canonical set of fields the engine knows how to pull from a document. */
enum FieldName: string
{
    case PrincipalAmount = 'principal_amount';
    case InterestRate = 'interest_rate';
    case IssueDate = 'issue_date';
    case DueDate = 'due_date';
    case DebtorTaxId = 'debtor_tax_id';
    case CreditorTaxId = 'creditor_tax_id';
    case DocumentNumber = 'document_number';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
