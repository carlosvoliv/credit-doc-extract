<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Opaque identity for a credit document. A UUID v4 generated in the domain so
 * identity never depends on the persistence layer (no auto-increment leakage).
 */
final readonly class DocumentId
{
    private function __construct(public string $value)
    {
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value)) {
            throw new InvalidArgumentException("Invalid document id: {$value}");
        }
    }

    public static function generate(): self
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0F) | 0x40); // version 4
        $b[8] = chr((ord($b[8]) & 0x3F) | 0x80); // variant

        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4)));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
