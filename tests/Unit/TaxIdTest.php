<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\CreditDocument\Domain\ValueObject\TaxId;

final class TaxIdTest extends TestCase
{
    public function test_accepts_a_valid_cpf_and_classifies_it_as_individual(): void
    {
        $taxId = new TaxId('529.982.247-25');

        $this->assertTrue($taxId->isIndividual());
        $this->assertSame('529982247-25', substr($taxId->digits, 0, 9).'-'.substr($taxId->digits, -2));
        $this->assertSame('529.***.***-25', $taxId->masked());
    }

    public function test_accepts_a_valid_cnpj_and_classifies_it_as_entity(): void
    {
        $taxId = new TaxId('11.222.333/0001-81');

        $this->assertFalse($taxId->isIndividual());
        $this->assertSame(TaxId::ENTITY, $taxId->kind);
    }

    public function test_rejects_an_invalid_checksum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaxId('111.444.777-00');
    }

    public function test_rejects_repeated_digits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaxId('111.111.111-11');
    }

    public function test_try_parse_returns_null_instead_of_throwing(): void
    {
        $this->assertNull(TaxId::tryParse('not-a-tax-id'));
    }
}
