<template>
  <PrestadorLayout title="Minha Agenda">

    <!-- Faltas pendentes de justificativa -->
    <div v-if="faltasPendentes.length" class="mb-5 space-y-3">
      <div v-for="s in faltasPendentes" :key="s.id"
        class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-800">
          Falta registrada em {{ formatarData(s.date) }} — {{ s.service_name }} ({{ s.company_name }})
        </p>
        <p class="text-xs text-red-700 mt-1">
          Você não fez check-in neste agendamento. Justifique até
          {{ formatarPrazo(s.no_show_justification_deadline) }} para evitar o bloqueio temporário de candidatura a novas vagas.
        </p>
        <textarea v-model="justificativas[s.id]"
          rows="2" maxlength="1000"
          placeholder="Explique o motivo da ausência..."
          class="mt-3 w-full text-sm border border-red-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-red-300"></textarea>
        <button @click="enviarJustificativa(s.id)"
          :disabled="!justificativas[s.id]?.trim()"
          class="mt-2 text-sm font-medium bg-red-600 text-white px-4 py-1.5 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
          Enviar justificativa
        </button>
      </div>
    </div>

    <!-- Header: tabs de modo + navegação -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
      <div class="flex rounded-lg border border-gray-200 overflow-hidden self-start">
        <button v-for="m in modos" :key="m.key"
          @click="mudarModo(m.key)"
          :class="[
            'px-4 py-2 text-sm font-medium transition',
            modoAtual === m.key
              ? 'bg-orange-500 text-white'
              : 'bg-white text-gray-600 hover:bg-gray-100',
          ]">
          {{ m.label }}
        </button>
      </div>

      <div class="flex items-center gap-3">
        <button @click="navegar(-1)"
          class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h2 class="text-base font-bold text-gray-800 capitalize min-w-44 text-center">{{ periodoLabel }}</h2>
        <button @click="navegar(1)"
          class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Vista: Semana -->
    <div v-if="modoAtual === 'semana'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <div class="grid grid-cols-7 min-w-[560px]">
          <div v-for="dia in diasDaSemana" :key="dia.dateStr"
            @click="selecionarDia(dia.dateStr)"
            :class="[
              'border-r border-gray-100 last:border-r-0 cursor-pointer hover:bg-orange-50 transition',
              dia.dateStr === diaSelecionadoStr ? 'bg-orange-50 ring-2 ring-inset ring-orange-400' : '',
            ]">
            <div :class="['py-3 text-center border-b border-gray-100', dia.isHoje ? 'bg-orange-500' : 'bg-gray-50']">
              <p :class="['text-xs font-semibold uppercase tracking-wider', dia.isHoje ? 'text-white' : 'text-gray-500']">
                {{ dia.diaSemana }}
              </p>
              <p :class="['text-lg font-bold', dia.isHoje ? 'text-white' : 'text-gray-800']">
                {{ dia.diaNum }}
              </p>
            </div>
            <div class="p-1.5 space-y-1 min-h-24">
              <div v-for="s in dia.schedules" :key="s.id"
                class="text-xs bg-orange-100 text-orange-800 rounded px-1.5 py-1 truncate">
                <span class="font-medium">{{ s.start_time }}</span> {{ s.service_name }}
              </div>
              <div v-if="!dia.schedules.length" class="text-xs text-gray-300 text-center pt-3">—</div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <DetalhesDia :dateStr="diaSelecionadoStr" :label="diaSelecionadoLabel" :items="agendamentosDiaSelecionado" />
        <ResumoCard titulo="Resumo da semana" :total="schedules.length" :horas="horasTotais" />
      </div>
    </div>

    <!-- Vista: Mês -->
    <div v-else-if="modoAtual === 'mes'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
          <div v-for="d in diasSemana" :key="d"
            class="py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
            {{ d }}
          </div>
        </div>
        <div class="grid grid-cols-7">
          <div v-for="(cell, i) in celulas" :key="i"
            @click="cell.dia ? selecionarDia(cell.dataStr) : null"
            :class="[
              'min-h-16 border-b border-r border-gray-100 p-1.5 transition',
              cell.dia ? 'cursor-pointer hover:bg-orange-50' : 'bg-gray-50',
              cell.dataStr && cell.dataStr === diaSelecionadoStr ? 'ring-2 ring-inset ring-orange-400' : '',
              cell.dia === hoje.dia && mesAtual === hoje.mes && anoAtual === hoje.ano ? 'bg-orange-50' : '',
            ]">
            <div v-if="cell.dia">
              <span :class="[
                'text-xs font-medium inline-flex w-6 h-6 items-center justify-center rounded-full',
                cell.dia === hoje.dia && mesAtual === hoje.mes && anoAtual === hoje.ano
                  ? 'bg-orange-500 text-white' : 'text-gray-700',
              ]">{{ cell.dia }}</span>
              <div class="flex flex-wrap gap-0.5 mt-0.5">
                <div v-for="s in (agendamentosPorDia[cell.dataStr] ?? []).slice(0, 3)" :key="s.id"
                  class="w-1.5 h-1.5 rounded-full"
                  :class="s.status === 'completed' ? 'bg-green-500' : 'bg-orange-400'" />
                <span v-if="(agendamentosPorDia[cell.dataStr] ?? []).length > 3"
                  class="text-xs text-gray-400">+{{ (agendamentosPorDia[cell.dataStr] ?? []).length - 3 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <DetalhesDia :dateStr="diaSelecionadoStr" :label="diaSelecionadoLabel" :items="agendamentosDiaSelecionado" />
        <ResumoCard titulo="Resumo do mês" :total="schedules.length" :horas="horasTotais" />
      </div>
    </div>

    <!-- Vista: Ano -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2">
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
          <button v-for="m in mesesDoAno" :key="m.mes"
            @click="irParaMes(m.mes)"
            :class="[
              'bg-white rounded-xl border p-4 text-center hover:shadow-md transition cursor-pointer',
              m.isAtual ? 'border-orange-400 ring-2 ring-orange-300' : 'border-gray-200',
            ]">
            <p class="text-sm font-semibold text-gray-700 capitalize">{{ m.nome }}</p>
            <p :class="['text-2xl font-bold mt-1', m.count ? 'text-orange-600' : 'text-gray-300']">
              {{ m.count || '—' }}
            </p>
            <p v-if="m.count" class="text-xs text-gray-400 mt-0.5">
              {{ m.count === 1 ? 'agendamento' : 'agendamentos' }}
            </p>
          </button>
        </div>
      </div>

      <div>
        <ResumoCard titulo="Resumo do ano" :total="schedules.length" :horas="horasTotais" />
      </div>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { router } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  schedules:   Array,
  mes:         Number,
  ano:         Number,
  modo:        String,
  data_inicio: String,
})

const faltasPendentes = computed(() => props.schedules.filter(s => s.no_show_status === 'pending_justification'))
const justificativas = ref({})

function formatarData(dateStr) {
  const [y, m, d] = dateStr.split('-').map(Number)
  return new Date(y, m - 1, d).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatarPrazo(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function enviarJustificativa(scheduleId) {
  const texto = (justificativas.value[scheduleId] ?? '').trim()
  if (!texto) return
  router.post(route('prestador.agenda.justificarFalta', scheduleId), { justificativa: texto }, {
    preserveScroll: true,
    onSuccess: () => { justificativas.value[scheduleId] = '' },
  })
}

const modoAtual = ref(props.modo ?? 'mes')
const mesAtual  = ref(props.mes  ?? new Date().getMonth() + 1)
const anoAtual  = ref(props.ano  ?? new Date().getFullYear())

const modos = [
  { key: 'semana', label: 'Semana' },
  { key: 'mes',    label: 'Mês'    },
  { key: 'ano',    label: 'Ano'    },
]

const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

function formatDate(d) {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const hoje = {
  dia:     new Date().getDate(),
  mes:     new Date().getMonth() + 1,
  ano:     new Date().getFullYear(),
  dataStr: formatDate(new Date()),
}

// Calcula o domingo da semana atual como fallback
function domingoAtual() {
  const d = new Date()
  d.setDate(d.getDate() - d.getDay())
  return formatDate(d)
}

const dataInicioAtual = computed(() => props.data_inicio ?? domingoAtual())

// ─── Label do período ──────────────────────────────────────────────────────
const periodoLabel = computed(() => {
  if (modoAtual.value === 'semana') {
    const start = new Date(dataInicioAtual.value + 'T00:00:00')
    const end   = new Date(start)
    end.setDate(end.getDate() + 6)
    const fmt = d => d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
    return `${fmt(start)} – ${fmt(end)}`
  }
  if (modoAtual.value === 'ano') return String(anoAtual.value)
  return new Date(anoAtual.value, mesAtual.value - 1, 1)
    .toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

// ─── Navegação ─────────────────────────────────────────────────────────────
function navegar(delta) {
  if (modoAtual.value === 'semana') {
    const d = new Date(dataInicioAtual.value + 'T00:00:00')
    d.setDate(d.getDate() + delta * 7)
    router.get(route('prestador.agenda.index'), { modo: 'semana', data_inicio: formatDate(d) }, { preserveState: false })
  } else if (modoAtual.value === 'mes') {
    let m = mesAtual.value + delta
    let a = anoAtual.value
    if (m > 12) { m = 1; a++ }
    if (m < 1)  { m = 12; a-- }
    router.get(route('prestador.agenda.index'), { modo: 'mes', mes: m, ano: a }, { preserveState: false })
  } else {
    router.get(route('prestador.agenda.index'), { modo: 'ano', ano: anoAtual.value + delta }, { preserveState: false })
  }
}

function mudarModo(novo) {
  if (novo === modoAtual.value) return
  const params = { modo: novo }
  if (novo === 'mes') { params.mes = mesAtual.value; params.ano = anoAtual.value }
  else if (novo === 'ano') { params.ano = anoAtual.value }
  router.get(route('prestador.agenda.index'), params, { preserveState: false })
}

function irParaMes(mes) {
  router.get(route('prestador.agenda.index'), { modo: 'mes', mes, ano: anoAtual.value }, { preserveState: false })
}

// ─── Mapa data → schedules ─────────────────────────────────────────────────
const agendamentosPorDia = computed(() => {
  const map = {}
  for (const s of props.schedules) {
    if (!map[s.date]) map[s.date] = []
    map[s.date].push(s)
  }
  return map
})

// ─── Vista Semana ──────────────────────────────────────────────────────────
const diasDaSemana = computed(() => {
  const start = new Date(dataInicioAtual.value + 'T00:00:00')
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(start)
    d.setDate(d.getDate() + i)
    const dateStr = formatDate(d)
    return {
      dateStr,
      diaSemana: diasSemana[d.getDay()],
      diaNum: d.getDate(),
      schedules: agendamentosPorDia.value[dateStr] ?? [],
      isHoje: dateStr === hoje.dataStr,
    }
  })
})

// ─── Vista Mês ─────────────────────────────────────────────────────────────
const celulas = computed(() => {
  const primeiroDia = new Date(anoAtual.value, mesAtual.value - 1, 1).getDay()
  const diasNoMes   = new Date(anoAtual.value, mesAtual.value, 0).getDate()
  const cells = []
  for (let i = 0; i < primeiroDia; i++) cells.push({ dia: null, dataStr: null })
  for (let d = 1; d <= diasNoMes; d++) {
    const dataStr = `${anoAtual.value}-${String(mesAtual.value).padStart(2,'0')}-${String(d).padStart(2,'0')}`
    cells.push({ dia: d, dataStr })
  }
  while (cells.length % 7 !== 0) cells.push({ dia: null, dataStr: null })
  return cells
})

// ─── Vista Ano ─────────────────────────────────────────────────────────────
const mesesDoAno = computed(() => {
  const counts = {}
  for (const s of props.schedules) {
    const m = parseInt(s.date.slice(5, 7))
    counts[m] = (counts[m] ?? 0) + 1
  }
  const nomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
  return nomes.map((nome, i) => ({
    mes: i + 1,
    nome,
    count: counts[i + 1] ?? 0,
    isAtual: i + 1 === hoje.mes && anoAtual.value === hoje.ano,
  }))
})

// ─── Seleção de dia ────────────────────────────────────────────────────────
const diaSelecionadoStr = ref(null)

function selecionarDia(dateStr) {
  diaSelecionadoStr.value = dateStr === diaSelecionadoStr.value ? null : dateStr
}

const diaSelecionadoLabel = computed(() => {
  if (!diaSelecionadoStr.value) return ''
  const [y, m, d] = diaSelecionadoStr.value.split('-').map(Number)
  return new Date(y, m - 1, d).toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' })
})

const agendamentosDiaSelecionado = computed(() =>
  diaSelecionadoStr.value ? (agendamentosPorDia.value[diaSelecionadoStr.value] ?? []) : []
)

// ─── Total de horas ────────────────────────────────────────────────────────
const horasTotais = computed(() => {
  let total = 0
  for (const s of props.schedules) {
    const [h1, m1] = s.start_time.split(':').map(Number)
    const [h2, m2] = s.end_time.split(':').map(Number)
    total += (h2 * 60 + m2 - h1 * 60 - m1)
  }
  const h = Math.floor(total / 60)
  const m = total % 60
  return m > 0 ? `${h}h${m}` : `${h}h`
})

// ─── Badge de status (falta / concluído / agendado) ───────────────────────
function badgeFalta(s) {
  if (s.no_show_status === 'unjustified') return { classe: 'bg-red-100 text-red-700', texto: 'Falta' }
  if (s.no_show_status === 'pending_justification') return { classe: 'bg-amber-100 text-amber-700', texto: 'Aguardando justificativa' }
  if (s.status === 'completed') return { classe: 'bg-green-100 text-green-700', texto: 'Concluído' }
  return { classe: 'bg-orange-100 text-orange-700', texto: 'Agendado' }
}

// ─── Componente: detalhe do dia ────────────────────────────────────────────
const DetalhesDia = {
  props: { dateStr: String, label: String, items: Array },
  setup(props) {
    return () => {
      if (!props.dateStr) {
        return h('div', { class: 'bg-white rounded-xl border border-gray-200 p-4 text-center text-gray-400 text-sm' },
          'Clique em um dia para ver os detalhes.')
      }
      return h('div', { class: 'bg-white rounded-xl border border-gray-200 p-4' }, [
        h('h3', { class: 'font-semibold text-gray-800 text-sm mb-3 capitalize' }, props.label),
        props.items.length
          ? h('div', { class: 'space-y-3' }, props.items.map(s =>
              h('div', { key: s.id, class: 'border border-gray-100 rounded-lg p-3' }, [
                h('div', { class: 'flex items-start justify-between gap-2' }, [
                  h('div', { class: 'flex-1 min-w-0' }, [
                    h('p', { class: 'font-medium text-gray-800 text-sm truncate' }, s.service_name),
                    h('p', { class: 'text-xs text-gray-500 truncate' }, s.company_name),
                  ]),
                  h('span', {
                    class: `text-xs px-1.5 py-0.5 rounded font-medium shrink-0 ${badgeFalta(s).classe}`
                  }, badgeFalta(s).texto),
                ]),
                h('div', { class: 'flex items-center gap-3 mt-2 text-xs text-gray-500' }, [
                  h('span', `${s.start_time} – ${s.end_time}`),
                  s.city ? h('span', `📍 ${s.city}/${s.state}`) : null,
                ]),
              ])
            ))
          : h('div', { class: 'text-center py-6 text-gray-400 text-sm' }, 'Nenhum agendamento neste dia.'),
      ])
    }
  },
}

// ─── Componente: resumo ────────────────────────────────────────────────────
const ResumoCard = {
  props: { titulo: String, total: Number, horas: String },
  setup(props) {
    return () => h('div', { class: 'bg-white rounded-xl border border-gray-200 p-4' }, [
      h('p', { class: 'text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3' }, props.titulo),
      h('div', { class: 'grid grid-cols-2 gap-3' }, [
        h('div', { class: 'bg-orange-50 rounded-lg p-3 text-center' }, [
          h('p', { class: 'text-2xl font-bold text-orange-600' }, props.total),
          h('p', { class: 'text-xs text-orange-700 mt-0.5' }, 'Agendamentos'),
        ]),
        h('div', { class: 'bg-green-50 rounded-lg p-3 text-center' }, [
          h('p', { class: 'text-2xl font-bold text-green-600' }, props.horas),
          h('p', { class: 'text-xs text-green-700 mt-0.5' }, 'Horas'),
        ]),
      ]),
    ])
  },
}
</script>
