<template>
  <PrestadorLayout :title="demand.title">

    <Link :href="route('prestador.demandas.index')" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Voltar para Buscar Demandas
    </Link>

    <div class="max-w-3xl bg-white rounded-xl border border-gray-200 p-5">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-bold text-gray-800 text-lg">{{ demand.title }}</h2>
            <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium">{{ demand.service?.name }}</span>
            <span v-if="demand.service?.requires_license" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Exige CNH</span>
            <span :class="statusClass(demand.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">{{ statusLabel(demand.status) }}</span>
          </div>
          <p class="text-sm text-gray-500 mt-1">{{ demand.company?.trade_name }}</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-sm text-gray-600">
        <span class="flex items-center gap-1.5">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          {{ formatDate(demand.date) }}
        </span>
        <span class="flex items-center gap-1.5">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ demand.start_time?.slice(0,5) }} – {{ demand.end_time?.slice(0,5) }}
          <span class="text-gray-400">({{ calcHoras(demand.start_time, demand.end_time) }}h)</span>
        </span>
        <span v-if="demand.city" class="flex items-center gap-1.5">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          {{ [demand.street, demand.number, demand.neighborhood, demand.city + '/' + demand.state].filter(Boolean).join(', ') }}
          <span v-if="demand.distance_km != null" class="text-orange-500 font-medium">({{ demand.distance_km }} km)</span>
        </span>
        <span class="flex items-center gap-1.5 text-gray-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          {{ demand.slots_confirmed }}/{{ demand.slots_needed }} vagas
        </span>
      </div>

      <p v-if="demand.description" class="text-sm text-gray-600 mt-4 border-t border-gray-100 pt-4">{{ demand.description }}</p>

      <p v-if="demand.service?.provider_rate" class="text-sm font-semibold text-green-600 mt-3">
        Receba ~R$ {{ calcValor(demand.service.provider_rate, demand.start_time, demand.end_time) }}
      </p>

      <div class="mt-5 pt-4 border-t border-gray-100">
        <!-- Já enviou proposta -->
        <div v-if="proposal" class="flex items-center gap-2">
          <span class="text-sm text-gray-500">Sua proposta:</span>
          <span :class="proposalClass(proposal.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
            {{ proposalLabel(proposal.status) }}
          </span>
        </div>

        <!-- Não aprovado ainda -->
        <p v-else-if="!providerApproved" class="text-sm text-gray-400 italic">
          Aguardando aprovação do seu cadastro para poder enviar propostas.
        </p>

        <!-- Exige CNH e não tem -->
        <p v-else-if="demand.service?.requires_license && !providerHasLicense" class="text-sm text-amber-600">
          Este serviço exige CNH. Atualize seu perfil para se candidatar.
        </p>

        <!-- Demanda não está mais disponível -->
        <p v-else-if="!['open', 'partially_scheduled'].includes(demand.status)" class="text-sm text-gray-400 italic">
          Esta demanda não está mais disponível para propostas.
        </p>

        <!-- Pode enviar proposta -->
        <button v-else @click="abrirModal"
          class="px-5 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition">
          Enviar Proposta
        </button>
      </div>
    </div>

    <!-- Modal envio de proposta -->
    <div v-if="modalAberto" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Enviar Proposta</h3>
        <p class="text-sm text-gray-500 mb-4">{{ demand.title }} · {{ demand.company?.trade_name }}</p>

        <label class="text-xs font-medium text-gray-500 block mb-1">Mensagem para a empresa (opcional)</label>
        <textarea v-model="propForm.message" rows="3" placeholder="Apresente-se, tire dúvidas sobre a demanda..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none mb-4" />

        <div class="flex gap-2">
          <button @click="modalAberto = false"
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
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  demand:              Object,
  proposal:            Object,
  providerApproved:    Boolean,
  providerHasLicense:  Boolean,
})

function statusClass(status) {
  return {
    open:                'bg-green-100 text-green-700',
    partially_scheduled: 'bg-blue-100 text-blue-700',
    scheduled:           'bg-purple-100 text-purple-700',
    cancelled:           'bg-gray-100 text-gray-500',
  }[status] ?? 'bg-gray-100 text-gray-500'
}
function statusLabel(status) {
  return { open: 'Aberta', partially_scheduled: 'Parcialmente agendada', scheduled: 'Agendada', cancelled: 'Cancelada' }[status] ?? status
}

const proposalMap = {
  pending:                { label: 'Enviada',          bg: 'bg-gray-100',  text: 'text-gray-600' },
  pending_admin_approval: { label: 'Em análise',       bg: 'bg-yellow-50', text: 'text-yellow-700' },
  pending_company_accept: { label: 'Aguard. empresa',  bg: 'bg-blue-50',   text: 'text-blue-700' },
  accepted:               { label: 'Aceita',           bg: 'bg-green-50',  text: 'text-green-700' },
  rejected:               { label: 'Recusada',         bg: 'bg-red-50',    text: 'text-red-600' },
  rejected_admin:         { label: 'Recusada (admin)', bg: 'bg-red-50',    text: 'text-red-600' },
}
function proposalClass(s) { const m = proposalMap[s]; return m ? `${m.bg} ${m.text}` : 'bg-gray-100 text-gray-500' }
function proposalLabel(s) { return proposalMap[s]?.label ?? s }

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

// Modal proposta
const modalAberto = ref(false)
const propForm = useForm({ demand_id: props.demand.id, message: '' })

function abrirModal() {
  propForm.message = ''
  modalAberto.value = true
}

function enviarProposta() {
  propForm.post(route('prestador.demandas.proposta'), {
    onSuccess: () => { modalAberto.value = false },
  })
}
</script>
