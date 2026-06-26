<template>
  <EmpresaLayout title="Minhas Demandas">

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-3 mb-5">
      <Link :href="route('empresa.demandas.create')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Demanda
      </Link>

      <!-- Filtro status -->
      <div class="flex gap-1 bg-white border border-gray-200 rounded-lg p-1">
        <button v-for="f in filtros" :key="f.value"
          @click="filtroAtivo = f.value"
          :class="filtroAtivo === f.value ? 'bg-teal-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
          class="px-3 py-1.5 rounded text-xs font-medium transition">
          {{ f.label }}
        </button>
      </div>
    </div>

    <!-- Lista -->
    <div v-if="demandasFiltradas.length" class="space-y-3">
      <div v-for="d in demandasFiltradas" :key="d.id"
        class="bg-white border border-gray-200 rounded-xl p-5 hover:border-teal-300 transition">

        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">
                {{ d.service?.name }}
              </span>
              <span :class="statusClass(d.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                {{ statusLabel(d.status) }}
              </span>
              <span v-if="d.pending_count > 0" class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                {{ d.pending_count }} proposta{{ d.pending_count > 1 ? 's' : '' }} nova{{ d.pending_count > 1 ? 's' : '' }}
              </span>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm">{{ d.title }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">
              {{ formatDate(d.date) }} · {{ d.start_time?.slice(0,5) }} – {{ d.end_time?.slice(0,5) }}
              · {{ d.city }}/{{ d.state }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
              {{ d.slots_confirmed }}/{{ d.slots_needed }} vagas confirmadas
              · {{ d.proposals_count }} proposta{{ d.proposals_count !== 1 ? 's' : '' }} recebida{{ d.proposals_count !== 1 ? 's' : '' }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link v-if="d.status !== 'cancelled'" :href="route('empresa.demandas.show', d.id)"
              class="px-3 py-1.5 border border-teal-600 text-teal-600 hover:bg-teal-50 text-xs font-medium rounded-lg transition">
              Ver propostas
            </Link>
            <button v-if="['open','partially_scheduled'].includes(d.status)"
              @click="cancelarDemanda(d)"
              class="px-3 py-1.5 border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium rounded-lg transition">
              Cancelar
            </button>
          </div>
        </div>

        <!-- Barra de progresso vagas -->
        <div class="mt-3">
          <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-teal-500 rounded-full transition-all"
              :style="{ width: d.slots_needed ? (d.slots_confirmed / d.slots_needed * 100) + '%' : '0%' }" />
          </div>
        </div>

      </div>
    </div>

    <!-- Vazio -->
    <div v-else class="text-center py-20">
      <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-gray-500 text-sm">Nenhuma demanda encontrada.</p>
      <Link :href="route('empresa.demandas.create')"
        class="mt-3 inline-block text-teal-600 text-sm font-medium hover:underline">
        Criar nova demanda
      </Link>
    </div>

    <!-- Modal confirmação cancelar -->
    <div v-if="demandaCancelar" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-40">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl">
        <h3 class="font-semibold text-gray-800 mb-2">Cancelar demanda?</h3>
        <p class="text-sm text-gray-500 mb-5">
          A demanda "<strong>{{ demandaCancelar.title }}</strong>" será cancelada e não aparecerá mais para prestadores.
        </p>
        <div class="flex gap-3">
          <button @click="demandaCancelar = null"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
            Voltar
          </button>
          <button @click="confirmarCancelar"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
            Cancelar demanda
          </button>
        </div>
      </div>
    </div>

  </EmpresaLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const props = defineProps({ demands: Array })

const filtros = [
  { label: 'Todas',      value: 'all' },
  { label: 'Abertas',    value: 'open' },
  { label: 'Em andamento', value: 'partially_scheduled' },
  { label: 'Concluídas', value: 'scheduled' },
  { label: 'Canceladas', value: 'cancelled' },
]
const filtroAtivo = ref('all')

const demandasFiltradas = computed(() =>
  filtroAtivo.value === 'all'
    ? props.demands
    : props.demands.filter(d => d.status === filtroAtivo.value)
)

function statusClass(status) {
  return {
    open:                  'bg-green-100 text-green-700',
    partially_scheduled:   'bg-blue-100 text-blue-700',
    scheduled:             'bg-purple-100 text-purple-700',
    cancelled:             'bg-gray-100 text-gray-500',
  }[status] ?? 'bg-gray-100 text-gray-500'
}

function statusLabel(status) {
  return {
    open:                  'Aberta',
    partially_scheduled:   'Parcialmente agendada',
    scheduled:             'Agendada',
    cancelled:             'Cancelada',
  }[status] ?? status
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const [y, m, d] = dateStr.slice(0, 10).split('-')
  return `${d}/${m}/${y}`
}

const demandaCancelar = ref(null)
function cancelarDemanda(d) { demandaCancelar.value = d }
function confirmarCancelar() {
  router.delete(route('empresa.demandas.destroy', demandaCancelar.value.id), {
    onFinish: () => { demandaCancelar.value = null },
  })
}
</script>
