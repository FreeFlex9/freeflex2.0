<template>
  <EmpresaLayout title="Check-in / Check-out">

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div class="min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Prestador</label>
        <input v-model="filtros.prestador" @input="filtrarComDebounce" type="text" placeholder="Nome do prestador..."
          class="w-full text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-teal-400" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Data</label>
        <input v-model="filtros.data" @change="filtrar" type="date"
          class="text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-teal-400" />
      </div>
      <button @click="limpar" class="text-sm text-teal-600 hover:underline self-end mb-0.5">Limpar</button>
    </div>

    <!-- Resultados -->
    <div v-if="pontos.data.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhum agendamento encontrado para os filtros selecionados.
    </div>

    <div v-else class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
            <th class="px-4 py-3">Data</th>
            <th class="px-4 py-3">Prestador</th>
            <th class="px-4 py-3">Demanda</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Check-in</th>
            <th class="px-4 py-3">Check-out</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in pontos.data" :key="p.id" class="border-b border-gray-50 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800">
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ formatDate(p.date) }}</td>
            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ p.provider?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ p.demand?.title ?? '—' }}</td>
            <td class="px-4 py-3"><StatusBadge :point="p" /></td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              <template v-if="p.check_in_at">
                {{ formatTime(p.check_in_at) }}
                <span v-if="p.check_in_distance_m !== null" class="block text-xs text-gray-400">{{ p.check_in_distance_m }}m do local</span>
              </template>
              <span v-else class="text-gray-300">—</span>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              <template v-if="p.check_out_at">
                {{ formatTime(p.check_out_at) }}
                <span v-if="p.check_out_distance_m !== null" class="block text-xs text-gray-400">{{ p.check_out_distance_m }}m do local</span>
              </template>
              <span v-else class="text-gray-300">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="pontos.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
      <button v-for="p in pontos.links" :key="p.label"
        :disabled="!p.url"
        @click="p.url && router.visit(p.url)"
        class="px-3 py-1.5 rounded-lg text-sm transition"
        :class="p.active
          ? 'bg-teal-600 text-white font-medium'
          : p.url
            ? 'border border-gray-200 text-gray-600 hover:bg-gray-50'
            : 'border border-gray-100 text-gray-300 cursor-not-allowed'"
        v-html="p.label" />
    </div>
  </EmpresaLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const StatusBadge = {
  props: { point: Object },
  computed: {
    cls() {
      if (this.point.check_out_at) return 'bg-green-100 text-green-700'
      if (this.point.check_in_at) return 'bg-blue-100 text-blue-700'
      return 'bg-orange-100 text-orange-700'
    },
    label() {
      if (this.point.check_out_at) return 'Concluído'
      if (this.point.check_in_at) return 'Em andamento'
      return 'Aguardando check-in'
    },
  },
  template: `<span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cls">{{ label }}</span>`,
}

const props = defineProps({
  pontos:  Object,
  filters: Object,
})

const filtros = ref({
  prestador: props.filters?.prestador ?? '',
  data:      props.filters?.data ?? '',
})

function formatDate(d) { return d ? new Date(d + 'T00:00:00').toLocaleDateString('pt-BR') : '-' }
function formatTime(t) { return t ? new Date(t).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '-' }

function filtrar() {
  router.get(route('empresa.pontos.index'), filtros.value, { preserveState: true, replace: true })
}

let timer = null
function filtrarComDebounce() {
  clearTimeout(timer)
  timer = setTimeout(filtrar, 400)
}

function limpar() {
  filtros.value = { prestador: '', data: '' }
  filtrar()
}
</script>
