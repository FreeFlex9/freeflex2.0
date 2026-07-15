<template>
  <AdminLayout title="Aprovações de Empresas" :pendentes="pendentes">
    <div class="mt-2">
      <div v-if="empresas.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
        Nenhuma empresa pendente de aprovação no momento.
      </div>

      <div v-else class="space-y-4">
        <div v-for="emp in empresas" :key="emp.id" class="bg-white rounded-xl shadow p-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-800 text-lg truncate">{{ emp.trade_name }}</h3>
              <div class="mt-1 space-y-0.5 text-sm text-gray-500">
                <p>CNPJ: <span class="text-gray-700 font-medium">{{ emp.cnpj }}</span></p>
                <p>E-mail: {{ emp.email }}</p>
                <p v-if="emp.phone">Telefone: {{ emp.phone }}</p>
                <p>Cadastro: {{ formatDate(emp.created_at) }}</p>
              </div>
            </div>

            <div class="flex flex-col gap-2 items-end">
              <a v-if="emp.cnpj_card_path" :href="'/storage/' + emp.cnpj_card_path" target="_blank"
                class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                Ver Cartão CNPJ
              </a>
              <span v-else class="text-xs px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full">
                Cartão CNPJ não enviado
              </span>

              <a v-if="emp.address_proof_path" :href="'/storage/' + emp.address_proof_path" target="_blank"
                class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                Ver Comprovante de Residência
              </a>
              <span v-else class="text-xs px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full">
                Comprovante de residência não enviado
              </span>

              <div class="flex gap-2 mt-2">
                <button @click="aprovar(emp)" :disabled="!emp.cnpj_card_path || !emp.address_proof_path || loading === emp.id"
                  class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                  Aprovar
                </button>
                <button @click="abrirRejeicao(emp)"
                  class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors">
                  Rejeitar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal confirmar aprovação -->
    <ConfirmModal
      :show="!!confirmAprovar"
      title="Aprovar empresa"
      :message="`Aprovar a empresa &quot;${confirmAprovar?.trade_name}&quot;? O acesso completo será liberado.`"
      confirm-text="Aprovar"
      variant="success"
      @confirm="confirmarAprovar"
      @cancel="confirmAprovar = null" />

    <!-- Modal rejeição -->
    <div v-if="modalEmpresa" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Rejeitar: {{ modalEmpresa.trade_name }}</h3>
        <textarea v-model="motivo" rows="4"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          placeholder="Descreva o motivo da rejeição..." />
        <p v-if="erroMotivo" class="text-sm text-red-600 mt-1">{{ erroMotivo }}</p>
        <div class="flex gap-2 mt-4">
          <button @click="fecharModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Cancelar</button>
          <button @click="rejeitar" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">Confirmar Rejeição</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({ empresas: Array })
const pendentes = { empresas: props.empresas.length, prestadores: 0, propostas: 0 }
const loading = ref(null)
const modalEmpresa = ref(null)
const confirmAprovar = ref(null)
const motivo = ref('')
const erroMotivo = ref('')

function formatDate(d) { return d ? new Date(d).toLocaleDateString('pt-BR') : '-' }

function aprovar(emp) {
  confirmAprovar.value = emp
}

function confirmarAprovar() {
  loading.value = confirmAprovar.value.id
  useForm({}).post(route('admin.empresas.aprovar', confirmAprovar.value.id), {
    onFinish: () => { loading.value = null; confirmAprovar.value = null },
  })
}

function abrirRejeicao(emp) { modalEmpresa.value = emp; motivo.value = ''; erroMotivo.value = '' }
function fecharModal() { modalEmpresa.value = null }

function rejeitar() {
  if (!motivo.value.trim()) { erroMotivo.value = 'O motivo é obrigatório.'; return }
  useForm({ motivo: motivo.value }).post(route('admin.empresas.rejeitar', modalEmpresa.value.id), {
    onSuccess: () => fecharModal(),
  })
}
</script>
