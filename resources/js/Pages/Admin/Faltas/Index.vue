<template>
  <AdminLayout title="Faltas">

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div class="min-w-[220px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select v-model="filtros.status" @change="filtrar"
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">Todos</option>
          <option value="pending_justification">Aguardando justificativa</option>
          <option value="justified">Justificada</option>
          <option value="unjustified">Não justificada (bloqueada)</option>
        </select>
      </div>
      <button @click="limpar" class="text-sm text-gray-500 underline self-end mb-0.5">Limpar</button>
    </div>

    <!-- Resultados -->
    <div v-if="faltas.data.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhuma falta encontrada para os filtros selecionados.
    </div>

    <div v-else class="bg-white rounded-xl shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
            <th class="px-4 py-3">Data</th>
            <th class="px-4 py-3">Prestador</th>
            <th class="px-4 py-3">Empresa / Demanda</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Justificativa</th>
            <th class="px-4 py-3">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="f in faltas.data" :key="f.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50 align-top">
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ formatDate(f.date) }}</td>
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ f.provider?.name ?? '—' }}
              <span class="block text-xs text-gray-400">{{ f.provider?.email }}</span>
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ f.demand?.company?.trade_name ?? '—' }}
              <span class="block text-xs text-gray-400">{{ f.demand?.title }}</span>
            </td>
            <td class="px-4 py-3"><StatusBadge :falta="f" /></td>
            <td class="px-4 py-3 text-gray-600 max-w-xs">
              <span v-if="f.no_show_justification">{{ f.no_show_justification }}</span>
              <span v-else class="text-gray-300">— não enviada —</span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <template v-if="f.no_show_status === 'pending_justification' && f.no_show_justification">
                <button @click="aprovar(f)" class="text-xs font-medium bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 mr-2">
                  Aprovar
                </button>
                <button @click="rejeitar(f)" class="text-xs font-medium bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700">
                  Rejeitar
                </button>
              </template>
              <template v-else-if="f.no_show_status === 'pending_justification'">
                <button @click="rejeitar(f)" class="text-xs font-medium bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700">
                  Aplicar bloqueio
                </button>
              </template>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="faltas.last_page > 1" class="mt-4 flex gap-2 justify-center">
      <Link v-for="link in faltas.links" :key="link.label" :href="link.url ?? '#'"
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
  props: { falta: Object },
  computed: {
    cls() {
      if (this.falta.no_show_status === 'unjustified') return 'bg-red-100 text-red-700'
      if (this.falta.no_show_status === 'justified') return 'bg-green-100 text-green-700'
      return 'bg-amber-100 text-amber-700'
    },
    label() {
      if (this.falta.no_show_status === 'unjustified') return 'Não justificada'
      if (this.falta.no_show_status === 'justified') return 'Justificada'
      return 'Aguardando justificativa'
    },
  },
  template: `<span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cls">{{ label }}</span>`,
}

const props = defineProps({
  faltas:  Object,
  filtros: Object,
})

const filtros = ref({
  status: props.filtros?.status ?? '',
})

function formatDate(d) { return d ? new Date(d + 'T00:00:00').toLocaleDateString('pt-BR') : '-' }

function filtrar() {
  router.get(route('admin.faltas.index'), filtros.value, { preserveState: true, replace: true })
}

function limpar() {
  filtros.value = { status: '' }
  filtrar()
}

function aprovar(f) {
  if (!confirm(`Aprovar a justificativa de ${f.provider?.name}? Nenhuma penalidade será aplicada.`)) return
  router.post(route('admin.faltas.aprovar', f.id), {}, { preserveScroll: true })
}

function rejeitar(f) {
  if (!confirm(`Rejeitar a justificativa de ${f.provider?.name}? O prestador ficará temporariamente impedido de se candidatar a novas vagas.`)) return
  router.post(route('admin.faltas.rejeitar', f.id), {}, { preserveScroll: true })
}
</script>
