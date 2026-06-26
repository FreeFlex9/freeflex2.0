<template>
  <PrestadorLayout title="Buscar Demandas">

    <!-- Aviso se não aprovado -->
    <div v-if="!providerApproved"
      class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div><strong>Cadastro em análise.</strong> Você poderá enviar propostas após ser aprovado pelo administrador.</div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        <input v-model="f.q" type="text" placeholder="Buscar por título, cidade..."
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
          @keydown.enter="applyFilters" />

        <select v-model="f.service_id"
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
          <option value="">Todos os serviços</option>
          <option v-for="s in allServices" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <select v-model="f.sort"
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
          <option value="">Ordenar: por data</option>
          <option value="distance">Ordenar: por distância</option>
        </select>

        <div class="flex gap-2">
          <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 flex-1">
            <input type="checkbox" v-model="f.my_services" class="rounded text-orange-500 focus:ring-orange-400" />
            Só meus serviços
          </label>
          <button @click="applyFilters"
            class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition">
            Filtrar
          </button>
        </div>

      </div>
    </div>

    <!-- Resultados -->
    <div v-if="demands.length === 0" class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      <p class="font-medium">Nenhuma demanda encontrada</p>
      <p class="text-xs mt-1">Tente ajustar os filtros ou aguarde novas demandas.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="d in demands" :key="d.id"
        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-200 transition-colors">

        <div class="flex flex-wrap items-start justify-between gap-3">
          <!-- Info principal -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-semibold text-gray-800">{{ d.title }}</h3>
              <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium">{{ d.service?.name }}</span>
              <span v-if="d.service?.requires_license"
                class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Exige CNH</span>
            </div>

            <p class="text-sm text-gray-500 mt-1">{{ d.company?.trade_name }}</p>

            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-600">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ formatDate(d.date) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ d.start_time?.slice(0,5) }} – {{ d.end_time?.slice(0,5) }}
                <span class="text-gray-400">({{ calcHoras(d.start_time, d.end_time) }}h)</span>
              </span>
              <span v-if="d.city" class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ d.neighborhood ? d.neighborhood + ', ' : '' }}{{ d.city }}/{{ d.state }}
                <span v-if="d.distance_km != null" class="text-orange-500 font-medium">({{ d.distance_km }} km)</span>
              </span>
              <span class="flex items-center gap-1 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ d.slots_confirmed }}/{{ d.slots_needed }} vagas
              </span>
            </div>

            <p v-if="d.description" class="text-sm text-gray-500 mt-2 line-clamp-2">{{ d.description }}</p>

            <!-- Valor estimado -->
            <p v-if="d.service?.provider_rate" class="text-sm font-semibold text-green-600 mt-2">
              Receba ~R$ {{ calcValor(d.service.provider_rate, d.start_time, d.end_time) }}
            </p>
          </div>

          <!-- Botão proposta -->
          <div class="shrink-0">
            <button v-if="providerApproved"
              @click="abrirModal(d)"
              class="px-5 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition">
              Enviar Proposta
            </button>
            <span v-else class="text-xs text-gray-400 italic">Aguardando aprovação</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal envio de proposta -->
    <div v-if="modalDemanda" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Enviar Proposta</h3>
        <p class="text-sm text-gray-500 mb-4">{{ modalDemanda.title }} · {{ modalDemanda.company?.trade_name }}</p>

        <!-- Resumo da demanda -->
        <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-gray-600 space-y-1">
          <p>📅 {{ formatDate(modalDemanda.date) }} · {{ modalDemanda.start_time?.slice(0,5) }} – {{ modalDemanda.end_time?.slice(0,5) }}</p>
          <p v-if="modalDemanda.city">📍 {{ modalDemanda.city }}/{{ modalDemanda.state }}</p>
          <p v-if="modalDemanda.service?.provider_rate" class="text-green-600 font-medium">
            💰 Valor estimado: R$ {{ calcValor(modalDemanda.service.provider_rate, modalDemanda.start_time, modalDemanda.end_time) }}
          </p>
        </div>

        <label class="text-xs font-medium text-gray-500 block mb-1">Mensagem para a empresa (opcional)</label>
        <textarea v-model="propForm.message" rows="3" placeholder="Apresente-se, tire dúvidas sobre a demanda..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none mb-4" />

        <div class="flex gap-2">
          <button @click="fecharModal"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
            Cancelar
          </button>
          <button @click="enviarProposta" :disabled="propForm.processing"
            class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 disabled:opacity-60">
            {{ propForm.processing ? 'Enviando...' : 'Confirmar Proposta' }}
          </button>
        </div>
      </div>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  demands:         Array,
  myServices:      Array,
  allServices:     Array,
  filters:         Object,
  providerApproved: Boolean,
})

// Filtros reativos
const f = reactive({
  q:           props.filters?.q           ?? '',
  service_id:  props.filters?.service_id  ?? '',
  sort:        props.filters?.sort        ?? '',
  my_services: props.filters?.my_services ?? false,
})

function applyFilters() {
  router.get(route('prestador.demandas.index'), {
    q:           f.q           || undefined,
    service_id:  f.service_id  || undefined,
    sort:        f.sort        || undefined,
    my_services: f.my_services || undefined,
  }, { preserveState: true, replace: true })
}

// Modal proposta
const modalDemanda = ref(null)
const propForm     = useForm({ demand_id: null, message: '' })

function abrirModal(d) {
  modalDemanda.value = d
  propForm.demand_id = d.id
  propForm.message   = ''
}
function fecharModal() { modalDemanda.value = null }

function enviarProposta() {
  propForm.post(route('prestador.demandas.proposta'), {
    onSuccess: () => fecharModal(),
  })
}

// Helpers
function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.slice(0, 10).split('-')
  return new Date(Number(y), Number(m) - 1, Number(day)).toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit' })
}

function calcHoras(ini, fim) {
  if (!ini || !fim) return 0
  const [h1, m1] = ini.split(':').map(Number)
  const [h2, m2] = fim.split(':').map(Number)
  return (((h2 * 60 + m2) - (h1 * 60 + m1)) / 60).toFixed(1)
}

function calcValor(rate, ini, fim) {
  const h = parseFloat(calcHoras(ini, fim))
  return (h * parseFloat(rate)).toFixed(2).replace('.', ',')
}
</script>
