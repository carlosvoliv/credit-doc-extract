<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Persistence model for an extracted document. Deliberately anaemic — it is a
 * data-mapper row, not where domain behaviour lives (that's the aggregate in
 * src/CreditDocument/Domain). The repository translates between the two.
 */
final class ExtractedDocumentRecord extends Model
{
    protected $table = 'extracted_documents';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'type', 'confidence', 'fields'];

    protected $casts = [
        'fields' => 'array',
        'confidence' => 'float',
    ];
}
