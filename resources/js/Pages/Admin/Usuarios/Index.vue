<template>
  <AdminLayout title="Gerenciar Usuários" :pendentes="pendentes">
    <!-- Abas de tipo -->
    <div class="flex gap-1 border-b border-gray-200 mb-4">
      <button @click="setTipo('prestador')"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="filtros.tipo === 'prestador'
          ? 'border-green-500 text-green-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'">
        Prestadores
      </button>
      <button @click="setTipo('empresa')"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="filtros.tipo === 'empresa'
          ? 'border-green-500 text-green-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'">
        Empresas
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[240px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">Pesquisar</label>
        <input v-model="filtros.search" @input="filtrarComDebounce" type="text"
          :placeholder="filtros.tipo === 'empresa'
            ? 'Nome, e-mail, CNPJ ou telefone...'
            : 'Nome, e-mail, CPF ou telefone...'"
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select v-model="filtros.status" @change="filtrar"
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">Todos</option>
          <option value="pending">Pendente</option>
          <option value="approved">Aprovado</option>
          <option value="rejected">Rejeitado</option>
        </select>
      </div>
      <button @click="limpar" class="text-sm text-gray-500 underline self-end mb-0.5">Limpar</button>
    </div>

    <!-- Resultados -->
    <div v-if="usuarios.data.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhum usuário encontrado para os filtros selecionados.
    </div>

    <div v-else class="bg-white rounded-xl shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
            <th class="px-4 py-3">{{ filtros.tipo === 'empresa' ? 'Razão Social' : 'Nome' }}</th>
            <th class="px-4 py-3">{{ filtros.tipo === 'empresa' ? 'CNPJ' : 'CPF' }}</th>
            <th class="px-4 py-3">E-mail</th>
            <th class="px-4 py-3">Telefone</th>
            <th class="px-4 py-3">Cidade/UF</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Cadastro</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuarios.data" :key="u.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ u.trade_name ?? u.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ formatDoc(u.cnpj ?? u.cpf) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.email }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.phone ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.city ? `${u.city}/${u.state ?? ''}` : '—' }}</td>
            <td class="px-4 py-3"><StatusBadge :status="u.status" /></td>
            <td class="px-4 py-3 text-gray-500">{{ formatDate(u.created_at) }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="abrirExclusao(u)"
                class="text-xs px-2.5 py-1 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="usuarios.last_page > 1" class="mt-4 flex gap-2 justify-center">
      <Link v-for="link in usuarios.links" :key="link.label" :href="link.url ?? '#'"
        :class="['px-3 py-1 text-sm rounded border', link.active ? 'bg-green-500 text-white border-green-500' : 'border-gray-300 text-gray-600 hover:bg-gray-50']"
        v-html="link.label" />
    </div>

    <!-- Modal de exclusão permanente -->
    <div v-if="modalExcluir" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Excluir permanentemente</h3>
        <p class="text-sm text-gray-500 mb-4">
          Isso remove <strong>{{ nomeExcluir }}</strong> e todos os dados vinculados (documentos, propostas,
          agendamentos, avaliações) de forma <strong>irreversível</strong>. O e-mail/CPF/CNPJ ficará livre para um
          novo cadastro.
        </p>
        <label class="block text-xs font-medium text-gray-500 mb-1">
          Digite <strong>{{ nomeExcluir }}</strong> para confirmar
        </label>
        <input v-model="confirmacaoTexto" type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400" />
        <div class="flex gap-2 mt-4">
          <button @click="fecharExclusao" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">
            Cancelar
          </button>
          <button @click="confirmarExclusao" :disabled="!confirmacaoValida || excluindo"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed">
            Excluir permanentemente
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const StatusBadge = {
  props: { status: String },
  computed: {
    cls() {
      return {
        pending:  'bg-yellow-100 text-yellow-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-600',
      }[this.status] ?? 'bg-gray-100 text-gray-600'
    },
    label() {
      return {
        pending:  'Pendente',
        approved: 'Aprovado',
        rejected: 'Rejeitado',
      }[this.status] ?? this.status
    },
  },
  template: `<span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cls">{{ label }}</span>`,
}

const props = defineProps({
  usuarios:   Object,
  filtros:    Object,
  pendentes:  Object,
})

const filtros = ref({
  tipo:   props.filtros?.tipo ?? 'prestador',
  status: props.filtros?.status ?? '',
  search: props.filtros?.search ?? '',
})

function formatDate(d) { return d ? new Date(d).toLocaleDateString('pt-BR') : '-' }

function formatDoc(doc) {
  if (!doc) return '—'
  const digits = String(doc).replace(/\D/g, '')
  if (digits.length === 11) return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  if (digits.length === 14) return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  return doc
}

function filtrar() {
  router.get(route('admin.usuarios.index'), filtros.value, { preserveState: true, replace: true })
}

let timer = null
function filtrarComDebounce() {
  clearTimeout(timer)
  timer = setTimeout(filtrar, 400)
}

function setTipo(tipo) {
  filtros.value.tipo = tipo
  filtrar()
}

function limpar() {
  filtros.value = { tipo: filtros.value.tipo, status: '', search: '' }
  filtrar()
}

const modalExcluir     = ref(null)
const confirmacaoTexto = ref('')
const excluindo        = ref(false)

const nomeExcluir = computed(() => modalExcluir.value ? (modalExcluir.value.trade_name ?? modalExcluir.value.name) : '')
const confirmacaoValida = computed(() =>
  confirmacaoTexto.value.trim().toLowerCase() === nomeExcluir.value.trim().toLowerCase()
)

function abrirExclusao(u) {
  modalExcluir.value = u
  confirmacaoTexto.value = ''
}

function fecharExclusao() {
  modalExcluir.value = null
  confirmacaoTexto.value = ''
}

function confirmarExclusao() {
  if (!confirmacaoValida.value) return
  excluindo.value = true
  router.delete(route('admin.usuarios.destroy', [filtros.value.tipo, modalExcluir.value.id]), {
    preserveScroll: true,
    onFinish: () => { excluindo.value = false; fecharExclusao() },
  })
}
</script>
