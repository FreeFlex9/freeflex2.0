<template>
  <PrestadorLayout title="Minha Agenda">

    <!-- Navegação do mês -->
    <div class="flex items-center justify-between mb-5">
      <button @click="mudarMes(-1)"
        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <h2 class="text-lg font-bold text-gray-800 capitalize">{{ mesLabel }}</h2>
      <button @click="mudarMes(1)"
        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

      <!-- Calendário -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <!-- Cabeçalho dias da semana -->
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
          <div v-for="d in diasSemana" :key="d"
            class="py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
            {{ d }}
          </div>
        </div>

        <!-- Grade de dias -->
        <div class="grid grid-cols-7">
          <div v-for="(cell, i) in celulas" :key="i"
            @click="cell.dia ? selecionarDia(cell.dia) : null"
            :class="[
              'min-h-16 border-b border-r border-gray-100 p-1.5 transition',
              cell.dia ? 'cursor-pointer hover:bg-orange-50' : 'bg-gray-50',
              cell.dia === diaHoje && mesAtual === hoje.mes && anoAtual === hoje.ano ? 'bg-orange-50' : '',
              cell.dia === diaSelecionado ? 'ring-2 ring-inset ring-orange-400' : '',
            ]">
            <div v-if="cell.dia">
              <span :class="[
                'text-xs font-medium inline-flex w-6 h-6 items-center justify-center rounded-full',
                cell.dia === diaHoje && mesAtual === hoje.mes && anoAtual === hoje.ano
                  ? 'bg-orange-500 text-white'
                  : 'text-gray-700',
              ]">{{ cell.dia }}</span>

              <!-- Dots de agendamentos -->
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

      <!-- Detalhe do dia selecionado -->
      <div>
        <div v-if="diaSelecionado" class="bg-white rounded-xl border border-gray-200 p-4">
          <h3 class="font-semibold text-gray-800 text-sm mb-3">
            {{ diaSelecionadoLabel }}
          </h3>

          <div v-if="agendamentosDiaSelecionado.length" class="space-y-3">
            <div v-for="s in agendamentosDiaSelecionado" :key="s.id"
              class="border border-gray-100 rounded-lg p-3">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-800 text-sm truncate">{{ s.service_name }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ s.company_name }}</p>
                </div>
                <span :class="s.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                  class="text-xs px-1.5 py-0.5 rounded font-medium shrink-0">
                  {{ s.status === 'completed' ? 'Concluído' : 'Agendado' }}
                </span>
              </div>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                <span>{{ s.start_time }} – {{ s.end_time }}</span>
                <span v-if="s.city">📍 {{ s.city }}/{{ s.state }}</span>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6 text-gray-400 text-sm">
            Nenhum agendamento neste dia.
          </div>
        </div>

        <div v-else class="bg-white rounded-xl border border-gray-200 p-4 text-center text-gray-400 text-sm">
          Selecione um dia no calendário para ver os detalhes.
        </div>

        <!-- Resumo do mês -->
        <div class="mt-4 bg-white rounded-xl border border-gray-200 p-4">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Resumo do mês</p>
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-orange-50 rounded-lg p-3 text-center">
              <p class="text-2xl font-bold text-orange-600">{{ schedules.length }}</p>
              <p class="text-xs text-orange-700 mt-0.5">Agendamentos</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
              <p class="text-2xl font-bold text-green-600">{{ horasTotais }}</p>
              <p class="text-xs text-green-700 mt-0.5">Horas no mês</p>
            </div>
          </div>
        </div>
      </div>

    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  schedules: Array,
  mes:       Number,
  ano:       Number,
})

const mesAtual = ref(props.mes)
const anoAtual = ref(props.ano)

const hoje = {
  dia: new Date().getDate(),
  mes: new Date().getMonth() + 1,
  ano: new Date().getFullYear(),
}
const diaHoje = hoje.dia

const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const mesLabel = computed(() => {
  return new Date(anoAtual.value, mesAtual.value - 1, 1)
    .toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

// Mapa data → schedules
const agendamentosPorDia = computed(() => {
  const map = {}
  for (const s of props.schedules) {
    if (!map[s.date]) map[s.date] = []
    map[s.date].push(s)
  }
  return map
})

// Células do calendário (dias + espaços em branco)
const celulas = computed(() => {
  const primeiroDia = new Date(anoAtual.value, mesAtual.value - 1, 1).getDay()
  const diasNoMes   = new Date(anoAtual.value, mesAtual.value, 0).getDate()
  const cells = []

  for (let i = 0; i < primeiroDia; i++) cells.push({ dia: null, dataStr: null })
  for (let d = 1; d <= diasNoMes; d++) {
    const dataStr = `${anoAtual.value}-${String(mesAtual.value).padStart(2,'0')}-${String(d).padStart(2,'0')}`
    cells.push({ dia: d, dataStr })
  }
  // Completar última linha
  while (cells.length % 7 !== 0) cells.push({ dia: null, dataStr: null })
  return cells
})

const diaSelecionado  = ref(null)
const diaSelecionadoStr = computed(() => {
  if (!diaSelecionado.value) return null
  return `${anoAtual.value}-${String(mesAtual.value).padStart(2,'0')}-${String(diaSelecionado.value).padStart(2,'0')}`
})
const diaSelecionadoLabel = computed(() => {
  if (!diaSelecionado.value) return ''
  return new Date(anoAtual.value, mesAtual.value - 1, diaSelecionado.value)
    .toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' })
})
const agendamentosDiaSelecionado = computed(() =>
  diaSelecionadoStr.value ? (agendamentosPorDia.value[diaSelecionadoStr.value] ?? []) : []
)

function selecionarDia(d) {
  diaSelecionado.value = d === diaSelecionado.value ? null : d
}

function mudarMes(delta) {
  let m = mesAtual.value + delta
  let a = anoAtual.value
  if (m > 12) { m = 1; a++ }
  if (m < 1)  { m = 12; a-- }
  router.get(route('prestador.agenda.index'), { mes: m, ano: a }, { preserveState: false })
}

// Total de horas no mês
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
</script>
