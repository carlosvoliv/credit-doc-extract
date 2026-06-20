<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

use InvalidArgumentException;

/** A normalised 0..1 confidence for an extraction. */
final readonly class ConfidenceScore
{
    public function __construct(public float $value)
    {
        if ($value < 0 || $value > 1) {
            throw new InvalidArgumentException('Confidence must be between 0 and 1.');
        }
    }

    public static function high(): self
    {
        return new self(0.95);
    }

    public static function medium(): self
    {
        return new self(0.7);
    }

    public static function low(): self
    {
        return new self(0.4);
    }

    public function isAtLeast(float $threshold): bool
    {
        return $this->value >= $threshold;
    }
}
