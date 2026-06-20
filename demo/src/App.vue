<script setup>
import { ref, computed } from 'vue'
import {
  FacetButton,
  FacetCard,
  FacetTable,
  FacetChip,
  FacetAlert,
  FacetKpiCard,
} from 'facet-ui'

const SAMPLE = `NOTA PROMISSÓRIA nº 0001/2025

Aos quinze dias do mês de janeiro, pagarei por esta única via de NOTA
PROMISSÓRIA a quantia de valor principal de R$ 50.000,00 (cinquenta mil reais),
com juros de 1,89% ao mês.

Data de emissão: 15/01/2025
Data de vencimento: 15/01/2026

Emitente (devedor): João da Silva, CPF 529.982.247-25
Credor (beneficiário): Acme Fomento Mercantil, CNPJ 11.222.333/0001-81`

const text = ref(SAMPLE)
const loading = ref(false)
const error = ref('')
const result = ref(null)

const columns = [
  { key: 'label', label: 'Field' },
  { key: 'value', label: 'Value' },
  { key: 'confidence', label: 'Confidence', align: 'right' },
]

const confidencePct = computed(() =>
  result.value ? Math.round(result.value.confidence * 100) : 0,
)

function chipVariant(c) {
  if (c >= 0.9) return 'ok'
  if (c >= 0.6) return 'blue'
  return 'grey'
}

async function extract() {
  loading.value = true
  error.value = ''
  result.value = null
  try {
    const res = await fetch('/api/extractions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ text: text.value }),
    })
    if (!res.ok) {
      const body = await res.json().catch(() => ({}))
      throw new Error(body.message || `Request failed (${res.status})`)
    }
    result.value = await res.json()
  } catch (e) {
    error.value = e.message || 'Extraction failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <header class="hero">
      <div class="eyebrow">credit-doc-extract</div>
      <h1>Structured data from credit documents.</h1>
      <p class="sub">
        A Laravel + DDD extraction engine. Paste a credit instrument below — the
        API classifies it and pulls the principal, rate, dates and parties, each
        with a confidence score. UI built with the
        <strong>facet-ui</strong> design system.
      </p>
    </header>

    <main class="grid">
      <FacetCard title="Document text">
        <textarea
          id="doc-input"
          v-model="text"
          name="document"
          class="doc-input"
          aria-label="Document text"
          spellcheck="false"
          rows="14"
        />
        <div class="actions">
          <FacetButton variant="ghost" @click="text = SAMPLE">Reset sample</FacetButton>
          <FacetButton :loading="loading" @click="extract">Extract</FacetButton>
        </div>
      </FacetCard>

      <FacetCard title="Extraction result">
        <FacetAlert v-if="error" variant="error" title="Error">{{ error }}</FacetAlert>

        <div v-else-if="!result" class="placeholder">
          Run an extraction to see the structured output.
        </div>

        <div v-else class="result">
          <div class="result__top">
            <FacetChip :variant="chipVariant(result.confidence)">
              {{ result.type_label }}
            </FacetChip>
            <span class="result__id">{{ result.id }}</span>
          </div>

          <FacetKpiCard
            label="Overall confidence"
            :value="`${confidencePct}%`"
            :sub="`${result.fields.length} fields extracted`"
            accent="total"
          />

          <FacetTable :columns="columns" :rows="result.fields">
            <template #cell-confidence="{ value }">
              <FacetChip :variant="chipVariant(value)" size="sm">
                {{ Math.round(value * 100) }}%
              </FacetChip>
            </template>
          </FacetTable>
        </div>
      </FacetCard>
    </main>
  </div>
</template>
