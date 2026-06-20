<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Src\CreditDocument\Domain\ValueObject\Money;

final class MoneyTest extends TestCase
{
    public function test_parses_pt_br_notation(): void
    {
        $money = Money::parse('R$ 1.234,56');

        $this->assertNotNull($money);
        $this->assertSame(123456, $money->amountInCents);
        $this->assertSame('R$ 1.234,56', $money->format());
    }

    public function test_parses_machine_notation(): void
    {
        $this->assertSame(123456, Money::parse('1234.56')?->amountInCents);
    }

    public function test_returns_null_for_non_monetary_text(): void
    {
        $this->assertNull(Money::parse('no money here'));
    }

    public function test_stores_minor_units_without_float_drift(): void
    {
        // 0.1 + 0.2 in cents must be exact.
        $this->assertSame(30, (new Money(10))->amountInCents + (new Money(20))->amountInCents);
    }
}
