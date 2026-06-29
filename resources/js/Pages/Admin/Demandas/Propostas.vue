<template>
  <AdminLayout :title="`Propostas — Demanda #${demanda.id}`" :pendentes="pendentes">
    <div class="mb-4">
      <Link :href="route('admin.demandas.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Voltar às demandas</Link>
      <div class="mt-2 bg-white rounded-xl shadow p-4 text-sm text-gray-600">
        <span class="font-semibold text-gray-800">{{ demanda.title }}</span>
        &nbsp;|&nbsp; {{ demanda.company?.trade_name ?? '—' }}
        &nbsp;|&nbsp; Vagas: {{ demanda.slots_needed }} | Confirmados: {{ demanda.slots_confirmed }}
      </div>
    </div>

    <div v-if="propostas.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhuma proposta pendente de aprovação.
    </div>

    <div v-else class="space-y-3">
      <div v-for="prop in propostas" :key="prop.id" class="bg-white rounded-xl shadow p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800">{{ prop.provider?.name ?? '—' }}</p>
            <div class="mt-1 text-sm text-gray-500 space-y-0.5">
              <p>CPF: {{ prop.provider?.cpf ?? '—' }}</p>
              <p>Enviado em: {{ formatDate(prop.created_at) }}</p>
              <p v-if="prop.message" class="italic">"{{ prop.message }}"</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="aprovar(prop)"
              class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-colors">
              Aprovar
            </button>
            <button @click="abrirRejeicao(prop)"
              class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors">
              Rejeitar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal confirmar aprovação -->
    <ConfirmModal
      :show="!!confirmAprovar"
      title="Aprovar proposta"
      :message="`Aprovar a proposta de &quot;${confirmAprovar?.provider?.name}&quot;?`"
      confirm-text="Aprovar"
      variant="success"
      @confirm="confirmarAprovar"
      @cancel="confirmAprovar = null" />

    <!-- Modal rejeição -->
    <div v-if="modalProposta" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Rejeitar proposta de {{ modalProposta.provider?.name }}</h3>
        <textarea v-model="motivo" rows="3"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          placeholder="Motivo (opcional)" />
        <div class="flex gap-2 mt-4">
          <button @click="fecharModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">Cancelar</button>
          <button @click="rejeitar" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">Confirmar</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({ demanda: Object, propostas: Array })
const pendentes = { empresas: 0, prestadores: 0, propostas: props.propostas.length }
const modalProposta = ref(null)
const confirmAprovar = ref(null)
const motivo = ref('')

function formatDate(d) { return d ? new Date(d).toLocaleString('pt-BR') : '-' }

function aprovar(prop) {
  confirmAprovar.value = prop
}

function confirmarAprovar() {
  useForm({}).post(route('admin.demandas.propostas.aprovar', confirmAprovar.value.id), {
    onFinish: () => { confirmAprovar.value = null },
  })
}

function abrirRejeicao(prop) { modalProposta.value = prop; motivo.value = '' }
function fecharModal() { modalProposta.value = null }

function rejeitar() {
  useForm({ motivo: motivo.value }).post(route('admin.demandas.propostas.rejeitar', modalProposta.value.id), {
    onSuccess: () => fecharModal(),
  })
}
</script>
