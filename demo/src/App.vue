<script setup>
import { ref, computed } from 'vue'
import {
  FacetButton,
  FacetTable,
  FacetChip,
  FacetAlert,
  FacetStepper,
  FacetIcon,
  FacetIconButton,
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
const dark = ref(true)

const columns = [
  { key: 'label', label: 'Campo' },
  { key: 'value', label: 'Valor' },
  { key: 'confidence', label: 'Confiança', align: 'right' },
]

const confidencePct = computed(() =>
  result.value ? Math.round(result.value.confidence * 100) : 0,
)

const steps = computed(() => {
  const done = !!result.value
  return [
    { label: 'Recebido', state: 'done' },
    { label: 'Classificado', state: done ? 'done' : 'idle' },
    { label: 'Extraído', state: done ? 'done' : 'idle' },
    { label: 'Validado', state: done ? 'done' : 'idle' },
  ]
})

function chipVariant(c) {
  if (c >= 0.9) return 'ok'
  if (c >= 0.6) return 'blue'
  return 'grey'
}

function toggleTheme() {
  dark.value = !dark.value
  document.documentElement.dataset.theme = dark.value ? 'facet-dark' : ''
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
      throw new Error(body.message || `Falha na requisição (${res.status})`)
    }
    result.value = await res.json()
  } catch (e) {
    error.value = e.message || 'Extração falhou.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="shell">
    <!-- App rail -->
    <aside class="rail">
      <div class="rail__logo" title="credit-doc-extract">CD</div>
      <nav class="rail__nav">
        <FacetIconButton name="layers" title="Documentos" />
        <FacetIconButton name="clock" title="Histórico" />
        <FacetIconButton name="code" title="API" />
      </nav>
      <button class="rail__theme" :title="dark ? 'Tema claro' : 'Tema escuro'" @click="toggleTheme">
        {{ dark ? '☀' : '☾' }}
      </button>
    </aside>

    <!-- Main -->
    <div class="main">
      <header class="topbar">
        <div>
          <h1 class="title">Extração de Documentos de Crédito</h1>
          <p class="subtitle">Motor Laravel + DDD · classificação e extração estruturada</p>
        </div>
        <FacetChip variant="blue" size="sm">API conectada</FacetChip>
      </header>

      <div class="workspace">
        <!-- Input panel -->
        <section class="panel">
          <header class="panel__head">
            <FacetIcon name="edit" :size="15" />
            <span>Documento</span>
          </header>
          <div class="panel__body">
            <textarea
              id="doc-input"
              v-model="text"
              name="document"
              class="doc-input"
              aria-label="Texto do documento"
              spellcheck="false"
              rows="15"
            />
            <div class="actions">
              <FacetButton variant="ghost" @click="text = SAMPLE">Restaurar exemplo</FacetButton>
              <FacetButton :loading="loading" @click="extract">Extrair</FacetButton>
            </div>
          </div>
        </section>

        <!-- Result -->
        <section class="results">
          <FacetAlert v-if="error" variant="error" title="Erro">{{ error }}</FacetAlert>

          <div v-else-if="!result" class="empty">
            <FacetIcon name="layers" :size="28" />
            <p>Extraia um documento para ver os dados estruturados.</p>
          </div>

          <template v-else>
            <!-- Brand gradient summary card -->
            <div class="summary">
              <div class="summary__icon"><FacetIcon name="layers" :size="34" /></div>
              <div class="summary__body">
                <div class="summary__row">
                  <span class="summary__type">{{ result.type_label }}</span>
                  <span class="summary__id">{{ result.id }}</span>
                </div>
                <div class="summary__metrics">
                  <div class="metric">
                    <span class="metric__value">{{ confidencePct }}%</span>
                    <span class="metric__label">Confiança</span>
                  </div>
                  <div class="metric">
                    <span class="metric__value">{{ result.fields.length }}</span>
                    <span class="metric__label">Campos</span>
                  </div>
                </div>
                <FacetStepper :steps="steps" class="summary__steps" />
              </div>
            </div>

            <!-- Fields panel -->
            <section class="panel">
              <header class="panel__head">
                <FacetIcon name="check" :size="15" />
                <span>Campos extraídos</span>
              </header>
              <div class="panel__body panel__body--flush">
                <FacetTable :columns="columns" :rows="result.fields">
                  <template #cell-value="{ value }">
                    <span class="mono">{{ value }}</span>
                  </template>
                  <template #cell-confidence="{ value }">
                    <FacetChip :variant="chipVariant(value)" size="sm">
                      {{ Math.round(value * 100) }}%
                    </FacetChip>
                  </template>
                </FacetTable>
              </div>
            </section>
          </template>
        </section>
      </div>
    </div>
  </div>
</template>
