<script setup>
import { ref, computed } from 'vue'
import {
  FacetButton,
  FacetTable,
  FacetChip,
  FacetAlert,
  FacetStepper,
  FacetTabs,
  FacetSelect,
  FacetIcon,
} from 'facet-ui'
import themeManifest from 'facet-ui/themes.json'

const SAMPLE = `NOTA PROMISSÓRIA nº 0001/2025

Aos quinze dias do mês de janeiro, pagarei por esta única via de NOTA
PROMISSÓRIA a quantia de valor principal de R$ 50.000,00 (cinquenta mil reais),
com juros de 1,89% ao mês.

Data de emissão: 15/01/2025
Data de vencimento: 15/01/2026

Emitente (devedor): João da Silva, CPF 529.982.247-25
Credor (beneficiário): Acme Fomento Mercantil, CNPJ 11.222.333/0001-81`

// ── Theme picker — list comes straight from facet-ui's exported manifest ───
const THEMES = [
  { value: 'facet-dark', label: 'Facet Dark' },
  { value: '', label: 'Facet Light' },
  ...themeManifest.map((t) => ({ value: t.name, label: t.label })),
]
const theme = ref('facet-dark')
function applyTheme(value) {
  theme.value = value
  if (value) document.documentElement.dataset.theme = value
  else delete document.documentElement.dataset.theme
}

// ── Extraction ────────────────────────────────────────────────────────────
const mode = ref('text') // text | file
const text = ref(SAMPLE)
const file = ref(null)
const loading = ref(false)
const error = ref('')
const result = ref(null)

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

const canExtract = computed(() =>
  mode.value === 'text' ? text.value.trim().length > 0 : !!file.value,
)

function chipVariant(c) {
  if (c >= 0.9) return 'ok'
  if (c >= 0.6) return 'blue'
  return 'grey'
}

function pickFile(e) {
  const f = (e.dataTransfer || e.target).files?.[0]
  if (f) file.value = f
}

function humanSize(bytes) {
  return bytes < 1024 ? `${bytes} B` : `${(bytes / 1024).toFixed(1)} KB`
}

async function extract() {
  if (!canExtract.value) return
  loading.value = true
  error.value = ''
  result.value = null
  try {
    let options
    if (mode.value === 'file') {
      const form = new FormData()
      form.append('file', file.value)
      options = { method: 'POST', headers: { Accept: 'application/json' }, body: form }
    } else {
      options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ text: text.value }),
      }
    }
    const res = await fetch('/api/extractions', options)
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
    <!-- App rail: brand + source link, nothing fake -->
    <aside class="rail">
      <div class="rail__logo" title="credit-doc-extract">CD</div>
      <a
        class="rail__link"
        href="https://github.com/carlosvoliv/credit-doc-extract"
        target="_blank"
        rel="noopener"
        title="Código-fonte"
      >
        <FacetIcon name="code" :size="18" />
      </a>
    </aside>

    <div class="main">
      <header class="topbar">
        <div class="topbar__title">
          <h1 class="title">Extração de Documentos de Crédito</h1>
          <p class="subtitle">Motor Laravel + DDD · classificação e extração estruturada</p>
        </div>
        <div class="topbar__tools">
          <FacetChip variant="blue" size="sm">API conectada</FacetChip>
          <div class="theme-pick">
            <FacetSelect :model-value="theme" :options="THEMES" @update:model-value="applyTheme" />
          </div>
        </div>
      </header>

      <div class="workspace">
        <!-- Input panel -->
        <section class="panel">
          <header class="panel__head">
            <FacetIcon name="edit" :size="15" />
            <span>Documento</span>
          </header>
          <div class="panel__body">
            <FacetTabs
              :model-value="mode"
              :tabs="[
                { value: 'text', label: 'Colar texto' },
                { value: 'file', label: 'Importar arquivo' },
              ]"
              @update:model-value="mode = $event"
            />

            <textarea
              v-if="mode === 'text'"
              id="doc-input"
              v-model="text"
              name="document"
              class="doc-input"
              aria-label="Texto do documento"
              spellcheck="false"
              rows="14"
            />

            <label
              v-else
              class="dropzone"
              :class="{ 'dropzone--filled': file }"
              @dragover.prevent
              @drop.prevent="pickFile"
            >
              <input type="file" accept=".pdf,.txt,application/pdf,text/plain" hidden @change="pickFile" />
              <FacetIcon name="download" :size="26" />
              <template v-if="file">
                <strong>{{ file.name }}</strong>
                <span class="dropzone__hint">{{ humanSize(file.size) }} · clique para trocar</span>
              </template>
              <template v-else>
                <strong>Arraste um PDF ou clique para enviar</strong>
                <span class="dropzone__hint">PDF ou TXT, até 10 MB</span>
              </template>
            </label>

            <div class="actions">
              <FacetButton v-if="mode === 'text'" variant="ghost" @click="text = SAMPLE">
                Restaurar exemplo
              </FacetButton>
              <FacetButton :loading="loading" :disabled="!canExtract" @click="extract">
                Extrair
              </FacetButton>
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
            <div class="summary">
              <div class="summary__icon"><FacetIcon name="layers" :size="32" /></div>
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
                <div class="summary__steps"><FacetStepper :steps="steps" /></div>
              </div>
            </div>

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
