<template>
  <AdminLayout title="Gerenciar Usuários" :pendentes="pendentes">
    <!-- Abas de tipo -->
    <div class="flex gap-1 border-b border-gray-200 mb-4">
      <button @click="setTipo('todos')"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="filtros.tipo === 'todos'
          ? 'border-green-500 text-green-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'">
        Todos
      </button>
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
          :placeholder="placeholderBusca"
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
            <th v-if="filtros.tipo === 'todos'" class="px-4 py-3">Tipo</th>
            <th class="px-4 py-3">{{ filtros.tipo === 'empresa' ? 'Razão Social' : 'Nome' }}</th>
            <th class="px-4 py-3">{{ filtros.tipo === 'empresa' ? 'CNPJ' : filtros.tipo === 'prestador' ? 'CPF' : 'CPF/CNPJ' }}</th>
            <th class="px-4 py-3">E-mail</th>
            <th class="px-4 py-3">Telefone</th>
            <th class="px-4 py-3">Cidade/UF</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Situação da conta</th>
            <th class="px-4 py-3">Cadastro</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuarios.data" :key="`${u.tipo}-${u.id}`" class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
            <td v-if="filtros.tipo === 'todos'" class="px-4 py-3 text-gray-600">
              {{ u.tipo === 'empresa' ? 'Empresa' : 'Prestador' }}
            </td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ u.nome }}</td>
            <td class="px-4 py-3 text-gray-600">{{ formatDoc(u.documento) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.email }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.phone ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ u.city ? `${u.city}/${u.state ?? ''}` : '—' }}</td>
            <td class="px-4 py-3"><StatusBadge :status="u.status" /></td>
            <td class="px-4 py-3">
              <BloqueioBadge :usuario="u" />
            </td>
            <td class="px-4 py-3 text-gray-500">{{ formatDate(u.created_at) }}</td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
              <button v-if="contaBloqueada(u)" @click="desbloquear(u)"
                class="text-xs px-2.5 py-1 rounded-lg text-green-700 hover:bg-green-50 transition-colors">
                Desbloquear
              </button>
              <button v-else @click="abrirBloqueio(u)"
                class="text-xs px-2.5 py-1 rounded-lg text-amber-700 hover:bg-amber-50 transition-colors">
                Bloquear
              </button>
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

    <!-- Modal de bloqueio -->
    <div v-if="modalBloqueio" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Bloquear usuário</h3>
        <p class="text-sm text-gray-500 mb-4">
          Bloquear <strong>{{ modalBloqueio.nome }}</strong>. Enquanto bloqueado, o acesso à plataforma
          (login, candidaturas, criação de demandas etc.) fica impedido.
        </p>

        <div class="flex gap-1 mb-4 border border-gray-200 rounded-lg p-1">
          <button @click="bloqueioForm.tipo = 'temporario'"
            class="flex-1 text-sm py-1.5 rounded-md transition-colors"
            :class="bloqueioForm.tipo === 'temporario' ? 'bg-amber-500 text-white' : 'text-gray-600'">
            Temporário
          </button>
          <button @click="bloqueioForm.tipo = 'definitivo'"
            class="flex-1 text-sm py-1.5 rounded-md transition-colors"
            :class="bloqueioForm.tipo === 'definitivo' ? 'bg-red-600 text-white' : 'text-gray-600'">
            Definitivo
          </button>
        </div>

        <div v-if="bloqueioForm.tipo === 'temporario'" class="mb-4">
          <label class="block text-xs font-medium text-gray-500 mb-1">Período do bloqueio</label>
          <div class="flex gap-2 mb-2">
            <button v-for="dias in [7, 15, 30, 90]" :key="dias" @click="definirDias(dias)"
              class="text-xs px-2.5 py-1 rounded-lg border"
              :class="diasSelecionados === dias ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-300 text-gray-600'">
              {{ dias }} dias
            </button>
          </div>
          <input v-model="bloqueioForm.bloqueado_ate" type="datetime-local"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
        </div>

        <label class="block text-xs font-medium text-gray-500 mb-1">Motivo do bloqueio</label>
        <textarea v-model="bloqueioForm.motivo" rows="3"
          placeholder="Descreva o motivo (descumprimento das regras, infração, comportamento inadequado...)"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
        <p v-if="erroBloqueio" class="text-xs text-red-600 mt-1">{{ erroBloqueio }}</p>

        <div class="flex gap-2 mt-4">
          <button @click="fecharBloqueio" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">
            Cancelar
          </button>
          <button @click="confirmarBloqueio" :disabled="!bloqueioValido || bloqueando"
            class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 disabled:opacity-40 disabled:cursor-not-allowed">
            Bloquear
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

const BloqueioBadge = {
  props: { usuario: Object },
  computed: {
    bloqueado() { return !this.usuario.active },
    definitivo() { return this.bloqueado && !this.usuario.blocked_until },
    cls() {
      if (!this.bloqueado) return 'bg-green-100 text-green-700'
      return this.definitivo ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-700'
    },
    label() {
      if (!this.bloqueado) return 'Ativo'
      if (this.definitivo) return 'Bloqueado definitivamente'
      return `Bloqueado até ${new Date(this.usuario.blocked_until).toLocaleString('pt-BR')}`
    },
  },
  template: `
    <div>
      <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="cls">{{ label }}</span>
      <p v-if="bloqueado && usuario.block_reason" class="text-xs text-gray-400 mt-1 max-w-[220px] truncate" :title="usuario.block_reason">
        {{ usuario.block_reason }}
      </p>
    </div>
  `,
}

const props = defineProps({
  usuarios:   Object,
  filtros:    Object,
  pendentes:  Object,
})

const filtros = ref({
  tipo:   props.filtros?.tipo ?? 'todos',
  status: props.filtros?.status ?? '',
  search: props.filtros?.search ?? '',
})

const placeholderBusca = computed(() => {
  if (filtros.value.tipo === 'empresa') return 'Nome, e-mail, CNPJ ou telefone...'
  if (filtros.value.tipo === 'prestador') return 'Nome, e-mail, CPF ou telefone...'
  return 'Nome, e-mail, CPF/CNPJ ou telefone...'
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

const nomeExcluir = computed(() => modalExcluir.value ? modalExcluir.value.nome : '')
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
  router.delete(route('admin.usuarios.destroy', [modalExcluir.value.tipo, modalExcluir.value.id]), {
    preserveScroll: true,
    onFinish: () => { excluindo.value = false; fecharExclusao() },
  })
}

function contaBloqueada(u) {
  return !u.active
}

const modalBloqueio = ref(null)
const bloqueioForm  = ref({ tipo: 'temporario', bloqueado_ate: '', motivo: '' })
const diasSelecionados = ref(null)
const erroBloqueio = ref('')
const bloqueando   = ref(false)

const bloqueioValido = computed(() => {
  if (!bloqueioForm.value.motivo.trim()) return false
  if (bloqueioForm.value.tipo === 'temporario' && !bloqueioForm.value.bloqueado_ate) return false
  return true
})

function definirDias(dias) {
  diasSelecionados.value = dias
  const data = new Date(Date.now() + dias * 24 * 60 * 60 * 1000)
  data.setSeconds(0, 0)
  bloqueioForm.value.bloqueado_ate = new Date(data.getTime() - data.getTimezoneOffset() * 60000)
    .toISOString().slice(0, 16)
}

function abrirBloqueio(u) {
  modalBloqueio.value = u
  bloqueioForm.value = { tipo: 'temporario', bloqueado_ate: '', motivo: '' }
  diasSelecionados.value = null
  erroBloqueio.value = ''
}

function fecharBloqueio() {
  modalBloqueio.value = null
  erroBloqueio.value = ''
}

function confirmarBloqueio() {
  if (!bloqueioValido.value) return
  bloqueando.value = true
  erroBloqueio.value = ''
  router.post(route('admin.usuarios.bloquear', [modalBloqueio.value.tipo, modalBloqueio.value.id]), {
    tipo_bloqueio: bloqueioForm.value.tipo,
    motivo: bloqueioForm.value.motivo,
    bloqueado_ate: bloqueioForm.value.tipo === 'temporario' ? bloqueioForm.value.bloqueado_ate : null,
  }, {
    preserveScroll: true,
    onError: (errors) => { erroBloqueio.value = Object.values(errors)[0] ?? 'Erro ao bloquear usuário.' },
    onSuccess: () => fecharBloqueio(),
    onFinish: () => { bloqueando.value = false },
  })
}

function desbloquear(u) {
  if (!confirm(`Desbloquear "${u.nome}"? O acesso à plataforma será restabelecido imediatamente.`)) return
  router.post(route('admin.usuarios.desbloquear', [u.tipo, u.id]), {}, { preserveScroll: true })
}
</script>
