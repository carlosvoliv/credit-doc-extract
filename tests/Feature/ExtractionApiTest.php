<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExtractionApiTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE = "CONTRATO DE EMPRÉSTIMO nº 77/2025\n".
        'Valor principal de R$ 12.500,00, juros de 2,5% ao mês. '.
        'Emissão: 01/03/2025. Vencimento: 01/03/2026. '.
        'Devedor: CPF 529.982.247-25. Credor: CNPJ 11.222.333/0001-81.';

    public function test_extracts_fields_from_posted_text_and_persists_them(): void
    {
        $response = $this->postJson('/api/extractions', ['text' => self::SAMPLE]);

        $response->assertCreated()
            ->assertJsonPath('type', 'loan_contract')
            ->assertJsonPath('fields.0.label', 'Principal Amount');

        $id = $response->json('id');
        $this->assertDatabaseHas('extracted_documents', ['id' => $id, 'type' => 'loan_contract']);

        // The stored document is retrievable and reconstitutes identically.
        $this->getJson("/api/extractions/{$id}")
            ->assertOk()
            ->assertJsonPath('id', $id)
            ->assertJsonPath('type', 'loan_contract');
    }

    public function test_validation_fails_without_text_or_file(): void
    {
        $this->postJson('/api/extractions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_returns_404_for_unknown_document(): void
    {
        $this->getJson('/api/extractions/0b2d8c1e-1111-4a22-8333-444455556666')
            ->assertNotFound();
    }
}
