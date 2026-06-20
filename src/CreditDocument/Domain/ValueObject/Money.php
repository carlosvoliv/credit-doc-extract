<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Money as integer minor units (cents) to avoid float rounding — a classic
 * value-object invariant. Parses the common pt-BR notation "R$ 1.234,56" as
 * well as plain machine notation "1234.56".
 */
final readonly class Money
{
    public function __construct(
        public int $amountInCents,
        public string $currency = 'BRL',
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    /**
     * Parse a free-text monetary amount. Returns null when the string holds no
     * recognisable amount (callers decide whether that's an error).
     */
    public static function parse(string $raw, string $currency = 'BRL'): ?self
    {
        // Keep only digits and separators, then drop any stray separators left
        // dangling at the ends (e.g. a trailing comma from "R$ 12.500,00,").
        $s = trim(preg_replace('/[^\d.,]/', '', $raw) ?? '', " .,\t");
        if ($s === '') {
            return null;
        }

        // Decide the decimal separator: pt-BR uses comma last ("1.234,56"),
        // machine notation uses dot last ("1234.56").
        $lastComma = strrpos($s, ',');
        $lastDot = strrpos($s, '.');
        if ($lastComma !== false && $lastComma > ($lastDot === false ? -1 : $lastDot)) {
            $s = str_replace('.', '', $s);   // strip thousands
            $s = str_replace(',', '.', $s);  // comma → decimal point
        } else {
            $s = str_replace(',', '', $s);   // strip thousands
        }

        if (! is_numeric($s)) {
            return null;
        }

        return new self((int) round(((float) $s) * 100), $currency);
    }

    public function asFloat(): float
    {
        return $this->amountInCents / 100;
    }

    public function format(): string
    {
        $n = number_format($this->amountInCents / 100, 2, ',', '.');

        return "R$ {$n}";
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }
}
