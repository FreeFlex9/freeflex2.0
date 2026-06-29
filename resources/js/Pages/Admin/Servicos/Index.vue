<template>
  <AdminLayout title="Serviços" :pendentes="pendentes">
    <div class="flex justify-end mb-4">
      <button @click="abrirCriar" class="px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition-colors">
        + Novo Serviço
      </button>
    </div>

    <div v-if="servicos.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhum serviço cadastrado.
    </div>

    <div v-else class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
          <tr>
            <th class="px-4 py-3 text-left">Nome</th>
            <th class="px-4 py-3 text-right">Valor/hora</th>
            <th class="px-4 py-3 text-right">Repasse</th>
            <th class="px-4 py-3 text-center">Precisa CNH</th>
            <th class="px-4 py-3 text-center">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="sv in servicos" :key="sv.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ sv.name }}</td>
            <td class="px-4 py-3 text-right text-gray-600">{{ formatMoeda(sv.hourly_rate) }}</td>
            <td class="px-4 py-3 text-right text-gray-600">{{ formatMoeda(sv.provider_rate) }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="sv.requires_license ? 'text-green-600' : 'text-gray-400'">{{ sv.requires_license ? 'Sim' : 'Não' }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <button @click="abrirEditar(sv)" class="text-xs px-3 py-1 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">Editar</button>
                <button @click="excluir(sv)" class="text-xs px-3 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100">Excluir</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal confirmar exclusão -->
    <ConfirmModal
      :show="!!confirmDelete"
      title="Excluir serviço"
      :message="`Excluir o serviço &quot;${confirmDelete?.name}&quot;? Esta ação não pode ser desfeita.`"
      confirm-text="Excluir"
      variant="danger"
      @confirm="confirmarExclusao"
      @cancel="confirmDelete = null" />

    <!-- Modal criar/editar -->
    <div v-if="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ form.id ? 'Editar' : 'Novo' }} Serviço</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nome</label>
            <input v-model="form.nome" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
            <p v-if="erros.nome" class="text-xs text-red-600 mt-0.5">{{ erros.nome }}</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Valor/hora (R$)</label>
              <input v-model="form.valor_hora" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="erros.valor_hora" class="text-xs text-red-600 mt-0.5">{{ erros.valor_hora }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Repasse (R$)</label>
              <input v-model="form.valor_repasse" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="erros.valor_repasse" class="text-xs text-red-600 mt-0.5">{{ erros.valor_repasse }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.precisa_cnh" type="checkbox" id="cnh" class="rounded" />
            <label for="cnh" class="text-sm text-gray-700">Precisa CNH</label>
          </div>
        </div>
        <div class="flex gap-2 mt-5">
          <button @click="fecharModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">Cancelar</button>
          <button @click="salvar" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600">Salvar</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({ servicos: Array })
const pendentes = { empresas: 0, prestadores: 0, propostas: 0 }
const modal = ref(false)
const confirmDelete = ref(null)
const erros = reactive({})
const form = reactive({ id: null, nome: '', valor_hora: '', valor_repasse: '', precisa_cnh: false })

function formatMoeda(v) { return v != null ? Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '-' }

function abrirCriar() { Object.assign(form, { id: null, nome: '', valor_hora: '', valor_repasse: '', precisa_cnh: false }); modal.value = true }
function abrirEditar(sv) { Object.assign(form, { id: sv.id, nome: sv.name, valor_hora: sv.hourly_rate, valor_repasse: sv.provider_rate, precisa_cnh: !!sv.requires_license }); modal.value = true }
function fecharModal() { modal.value = false; Object.assign(erros, { nome: null, valor_hora: null, valor_repasse: null }) }

function validar() {
  let ok = true
  erros.nome = form.nome.trim() ? null : 'Nome obrigatório'; if (!form.nome.trim()) ok = false
  erros.valor_hora = form.valor_hora >= 0 ? null : 'Valor inválido'; if (form.valor_hora < 0) ok = false
  if (Number(form.valor_repasse) > Number(form.valor_hora)) { erros.valor_repasse = 'Repasse não pode exceder valor/hora'; ok = false }
  else erros.valor_repasse = null
  return ok
}

function salvar() {
  if (!validar()) return
  const f = useForm({ nome: form.nome, valor_hora: form.valor_hora, valor_repasse: form.valor_repasse, precisa_cnh: form.precisa_cnh })
  if (form.id) {
    f.put(route('admin.servicos.update', form.id), { onSuccess: () => fecharModal() })
  } else {
    f.post(route('admin.servicos.store'), { onSuccess: () => fecharModal() })
  }
}

function excluir(sv) {
  confirmDelete.value = sv
}

function confirmarExclusao() {
  useForm({}).delete(route('admin.servicos.destroy', confirmDelete.value.id), {
    onFinish: () => { confirmDelete.value = null },
  })
}
</script>
