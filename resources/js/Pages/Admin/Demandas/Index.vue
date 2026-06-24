<template>
  <AdminLayout title="Demandas" :pendentes="pendentes">
    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select v-model="filtros.status" @change="filtrar" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">Todos</option>
          <option value="open">Aberta</option>
          <option value="scheduled">Agendada</option>
          <option value="in_progress">Em andamento</option>
          <option value="completed">Concluída</option>
          <option value="cancelled">Cancelada</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Empresa</label>
        <input v-model="filtros.empresa" @input="filtrar" type="text" placeholder="Nome empresa..."
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Serviço</label>
        <input v-model="filtros.servico" @input="filtrar" type="text" placeholder="Nome serviço..."
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <button @click="limpar" class="text-sm text-gray-500 underline self-end mb-0.5">Limpar</button>
    </div>

    <div v-if="demandas.data.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhuma demanda encontrada.
    </div>

    <div v-else class="space-y-3">
      <div v-for="dem in demandas.data" :key="dem.id" class="bg-white rounded-xl shadow p-5">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-semibold text-gray-800">#{{ dem.id }} — {{ dem.title }}</h3>
              <StatusBadge :status="dem.status" />
            </div>
            <div class="mt-1 text-sm text-gray-500 space-y-0.5">
              <p>Empresa: <span class="text-gray-700">{{ dem.company?.trade_name ?? '—' }}</span></p>
              <p>Serviço: <span class="text-gray-700">{{ dem.service?.name ?? '—' }}</span></p>
              <p>Data: {{ formatDate(dem.date) }} | {{ dem.start_time }} – {{ dem.end_time }}</p>
              <p>Vagas: {{ dem.slots_needed }} | Confirmados: {{ dem.slots_confirmed }}</p>
              <p v-if="dem.description" class="truncate max-w-xs">{{ dem.description }}</p>
            </div>
          </div>
          <Link :href="route('admin.demandas.propostas', dem.id)"
            class="px-3 py-1.5 bg-indigo-50 text-indigo-700 text-sm rounded-lg hover:bg-indigo-100 transition-colors">
            Ver propostas
          </Link>
        </div>
      </div>
    </div>

    <!-- Paginação -->
    <div v-if="demandas.last_page > 1" class="mt-4 flex gap-2 justify-center">
      <Link v-for="link in demandas.links" :key="link.label" :href="link.url ?? '#'"
        :class="['px-3 py-1 text-sm rounded border', link.active ? 'bg-green-500 text-white border-green-500' : 'border-gray-300 text-gray-600 hover:bg-gray-50']"
        v-html="link.label" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const StatusBadge = {
  props: { status: String },
  computed: {
    cls() {
      return { open: 'bg-green-100 text-green-700', scheduled: 'bg-blue-100 text-blue-700', in_progress: 'bg-yellow-100 text-yellow-700', completed: 'bg-gray-100 text-gray-600', cancelled: 'bg-red-100 text-red-600' }[this.status] ?? 'bg-gray-100 text-gray-600'
    },
    label() {
      return { open: 'Aberta', scheduled: 'Agendada', in_progress: 'Em andamento', completed: 'Concluída', cancelled: 'Cancelada' }[this.status] ?? this.status
    }
  },
  template: `<span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cls">{{ label }}</span>`,
}

const props = defineProps({ demandas: Object, filters: Object })
const pendentes = { empresas: 0, prestadores: 0, propostas: 0 }
const filtros = ref({ status: props.filters?.status ?? '', empresa: props.filters?.empresa ?? '', servico: props.filters?.servico ?? '' })

let timer = null
function filtrar() {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get(route('admin.demandas.index'), filtros.value, { preserveState: true, replace: true })
  }, 400)
}
function limpar() { filtros.value = { status: '', empresa: '', servico: '' }; filtrar() }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('pt-BR') : '-' }
</script>
