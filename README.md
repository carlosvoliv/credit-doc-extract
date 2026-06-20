# Credit Doc Extract

A **Laravel + DDD** engine that turns unstructured **credit documents** into
typed, validated data — principal amount, interest rate, issue/due dates and the
debtor/creditor tax ids — each with a confidence score. Ships a REST API and a
Vue demo built on the [facet-ui](https://github.com/carlosvoliv/facet-ui) design
system.

> Generic by design. This is a clean reimplementation of a document-extraction
> domain from first principles — no proprietary code, client names, or real
> document schemas. It works on public instrument types (promissory notes, loan
> contracts, credit assignments, invoices) and public Brazilian id formats
> (CPF/CNPJ).

## Why it's interesting

The point isn't the regex — it's the **architecture**. The domain has zero
framework dependencies; Laravel is just the delivery mechanism. Swapping the
regex extractors for ML/LLM-backed ones would not touch a single line of the
domain or application layers.

```
                 Interface (Laravel)            Application                 Domain (pure PHP)
  HTTP ─▶ ExtractionController ─▶ ExtractDocumentHandler ─▶ ExtractionEngine ─▶ CreditDocument (aggregate)
                                          │                      │                   ├─ value objects: Money, TaxId,
                                          │                      │                   │   DocumentType, ConfidenceScore
                                          ▼                      ▼                   └─ ExtractedField
                                  DocumentTextReader[]    FieldExtractor[]
                                  (PlainText, Pdf)        (Amount, Rate, Date,
                                          │                TaxId, DocNumber)         ◀── contracts (interfaces)
                                          ▼                      ▲
                                  Infrastructure  ───────────────┘
                                  (smalot/pdfparser, Eloquent repository)
```

- **Domain** (`src/CreditDocument/Domain`) — the aggregate, value objects (each
  enforcing its own invariants: `TaxId` verifies CPF/CNPJ check digits on
  construction, `Money` stores integer cents) and the contracts.
- **Application** (`…/Application`) — the `ExtractDocument` use case.
- **Infrastructure** (`…/Infrastructure`) — regex extractors, a keyword
  classifier, PDF/plain-text readers, and an Eloquent-backed repository that
  maps the aggregate to/from the database.
- **Interface** (`app/Http`) — a thin controller; no domain logic.

## API

```bash
# Extract from raw text
curl -X POST http://127.0.0.1:8000/api/extractions \
  -H 'Accept: application/json' \
  -d 'text=NOTA PROMISSÓRIA nº 1. Valor principal de R$ 50.000,00, juros de 1,89% a.m. ...'

# Extract from an uploaded PDF/TXT
curl -X POST http://127.0.0.1:8000/api/extractions -F file=@contract.pdf

# Fetch a stored extraction
curl http://127.0.0.1:8000/api/extractions/{id}
```

```jsonc
{
  "id": "038ccff2-…",
  "type": "promissory_note",
  "type_label": "Promissory Note",
  "confidence": 0.91,
  "fields": [
    { "name": "principal_amount", "label": "Principal Amount", "value": "R$ 50.000,00", "confidence": 0.95 },
    { "name": "debtor_tax_id",   "label": "Debtor Tax Id",    "value": "529.***.***-25", "confidence": 0.95 }
  ]
}
```

## Run it

```bash
composer install
php artisan migrate
php artisan serve            # API on http://127.0.0.1:8000

# Demo UI (separate terminal) — consumes the API, built with facet-ui
cd demo && npm install && npm run dev   # http://localhost:5173
```

> The demo depends on `facet-ui` as a sibling checkout (`file:../../facet-ui`),
> so clone both repos side by side under the same parent folder.

## Tests

```bash
php artisan test     # domain unit tests + API feature tests
./vendor/bin/pint    # code style
```

Domain value objects, the extraction engine and the HTTP API are all covered.

## License

MIT
