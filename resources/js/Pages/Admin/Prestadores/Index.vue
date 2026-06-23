<template>
  <AdminLayout title="Aprovações de Prestadores" :pendentes="pendentes">
    <div class="mt-2">
      <div v-if="prestadores.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
        Nenhum prestador pendente de aprovação no momento.
      </div>

      <div v-else class="space-y-4">
        <div v-for="pres in prestadores" :key="pres.id" class="bg-white rounded-xl shadow p-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-800 text-lg">{{ pres.nome }}</h3>
              <div class="mt-1 space-y-0.5 text-sm text-gray-500">
                <p>CPF: {{ pres.cpf }}</p>
                <p>E-mail: {{ pres.email }}</p>
                <p v-if="pres.telefone">Tel: {{ pres.telefone }}</p>
                <p>Cadastro: {{ formatDate(pres.data_cadastro) }}</p>
              </div>

              <!-- Documentos -->
              <div class="mt-3 flex flex-wrap gap-2">
                <!-- CNH ou RG -->
                <template v-if="pres.possui_cnh">
                  <span class="text-xs font-medium text-gray-600">CNH{{ pres.cnh_digital ? ' digital' : '' }}:</span>
                  <DocBadge :path="pres.cnh_frente_path" :label="pres.cnh_digital ? 'Arquivo' : 'Frente'" />
                  <DocBadge v-if="!pres.cnh_digital" :path="pres.cnh_verso_path" label="Verso" />
                </template>
                <template v-else>
                  <span class="text-xs font-medium text-gray-600">RG:</span>
                  <DocBadge :path="pres.rg_frente_path" label="Frente" />
                  <DocBadge :path="pres.rg_verso_path" label="Verso" />
                </template>

                <!-- CCMEI -->
                <template v-if="pres.cnpj_mei">
                  <span class="text-xs font-medium text-gray-600">CCMEI:</span>
                  <DocBadge :path="pres.ccmei_path" label="CCMEI" />
                </template>

                <!-- Outros docs -->
                <template v-if="pres.documentos?.length">
                  <span class="text-xs font-medium text-gray-600">Outros:</span>
                  <DocBadge v-for="doc in pres.documentos" :key="doc.id" :path="doc.caminho_arquivo" :label="doc.nome_arquivo" />
                </template>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col gap-2 items-end">
              <div class="flex gap-2">
                <button
                  @click="aprovar(pres)"
                  :disabled="!docsOk(pres)"
                  class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  :title="docsOk(pres) ? 'Aprovar' : 'Documentos obrigatórios pendentes'"
                >
                  Aprovar
                </button>
                <button
                  @click="abrirRejeicao(pres)"
                  class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors"
                >
                  Rejeitar
                </button>
              </div>
              <p v-if="!docsOk(pres)" class="text-xs text-red-500">Docs obrigatórios pendentes</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal rejeição -->
    <div v-if="modalPrestador" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Rejeitar: {{ modalPrestador.nome }}</h3>
        <textarea
          v-model="motivo"
          rows="4"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          placeholder="Descreva o motivo..."
        />
        <p v-if="erroMotivo" class="text-sm text-red-600 mt-1">{{ erroMotivo }}</p>
        <div class="flex gap-2 mt-4">
          <button @click="fecharModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">Cancelar</button>
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

const DocBadge = {
  props: { path: String, label: String },
  template: `
    <a v-if="path" :href="'/' + path" target="_blank"
       class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
      {{ label }}
    </a>
    <span v-else class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">{{ label }} PENDENTE</span>
  `,
}

const props = defineProps({ prestadores: Array })
const pendentes = { empresas: 0, prestadores: props.prestadores.length, propostas: 0 }

const modalPrestador = ref(null)
const motivo = ref('')
const erroMotivo = ref('')

function formatDate(d) {
  return d ? new Date(d).toLocaleString('pt-BR') : '-'
}

function docsOk(pres) {
  if (pres.possui_cnh) {
    if (pres.cnh_digital) return !!pres.cnh_frente_path
    return !!(pres.cnh_frente_path && pres.cnh_verso_path)
  }
  if (!pres.rg_frente_path || !pres.rg_verso_path) return false
  if (pres.cnpj_mei && !pres.ccmei_path) return false
  return true
}

function aprovar(pres) {
  if (!confirm(`Aprovar o prestador "${pres.nome}"?`)) return
  useForm({}).post(route('admin.prestadores.aprovar', pres.id))
}

function abrirRejeicao(pres) {
  modalPrestador.value = pres
  motivo.value = ''
  erroMotivo.value = ''
}

function fecharModal() { modalPrestador.value = null }

function rejeitar() {
  if (!motivo.value.trim()) { erroMotivo.value = 'O motivo é obrigatório.'; return }
  useForm({ motivo: motivo.value }).post(route('admin.prestadores.rejeitar', modalPrestador.value.id), {
    onSuccess: () => fecharModal(),
  })
}
</script>
