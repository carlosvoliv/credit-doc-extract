<?php

declare(strict_types=1);

namespace Src\CreditDocument\Domain\ValueObject;

use InvalidArgumentException;

/**
 * A Brazilian taxpayer identifier — CPF (11 digits, individuals) or CNPJ
 * (14 digits, entities). The format and check-digit algorithm are public
 * standards. The value object guarantees a *valid* id can ever exist: the
 * checksum is verified on construction, so downstream code never re-validates.
 */
final readonly class TaxId
{
    public const INDIVIDUAL = 'individual';

    public const ENTITY = 'entity';

    public string $digits;

    public string $kind;

    public function __construct(string $raw)
    {
        $digits = preg_replace('/\D/', '', $raw) ?? '';

        $this->kind = match (strlen($digits)) {
            11 => self::INDIVIDUAL,
            14 => self::ENTITY,
            default => throw new InvalidArgumentException('Tax id must have 11 or 14 digits.'),
        };

        if (! self::checksumValid($digits)) {
            throw new InvalidArgumentException("Invalid tax id checksum: {$raw}");
        }

        $this->digits = $digits;
    }

    public static function tryParse(string $raw): ?self
    {
        try {
            return new self($raw);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public function isIndividual(): bool
    {
        return $this->kind === self::INDIVIDUAL;
    }

    // Masked for display — only the first and last digits survive.
    public function masked(): string
    {
        if ($this->isIndividual()) {
            return substr($this->digits, 0, 3).'.***.***-'.substr($this->digits, -2);
        }

        return substr($this->digits, 0, 2).'.***.***/****-'.substr($this->digits, -2);
    }

    private static function checksumValid(string $d): bool
    {
        return strlen($d) === 11 ? self::cpfValid($d) : self::cnpjValid($d);
    }

    private static function cpfValid(string $d): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $d)) {
            return false; // all-equal digits are formally invalid
        }
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $d[$i] * (($t + 1) - $i);
            }
            $check = (($sum * 10) % 11) % 10;
            if ($check !== (int) $d[$t]) {
                return false;
            }
        }

        return true;
    }

    private static function cnpjValid(string $d): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $d)) {
            return false;
        }
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        foreach ([12, 13] as $pos) {
            $w = array_slice($weights, 13 - $pos);
            $sum = 0;
            for ($i = 0; $i < $pos; $i++) {
                $sum += (int) $d[$i] * $w[$i];
            }
            $mod = $sum % 11;
            $check = $mod < 2 ? 0 : 11 - $mod;
            if ($check !== (int) $d[$pos]) {
                return false;
            }
        }

        return true;
    }
}
