<template>
  <PrestadorLayout title="Minhas Propostas">

    <!-- Filtro por status -->
    <div class="flex gap-2 flex-wrap mb-5">
      <button v-for="opt in statusOpts" :key="opt.value"
        @click="setFiltro(opt.value)"
        :class="filtroAtivo === opt.value
          ? 'bg-orange-500 text-white border-orange-500'
          : 'bg-white text-gray-600 border-gray-300 hover:border-orange-300'"
        class="px-3 py-1.5 text-xs font-medium border rounded-full transition-colors">
        {{ opt.label }}
      </button>
    </div>

    <!-- Lista vazia -->
    <div v-if="propostasFiltradas.length === 0"
      class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="font-medium">Nenhuma proposta encontrada</p>
      <p class="text-xs mt-1">Busque demandas e envie sua primeira proposta!</p>
    </div>

    <!-- Cards de propostas -->
    <div v-else class="space-y-3">
      <div v-for="p in propostasFiltradas" :key="p.id"
        class="bg-white rounded-xl border p-5 transition-colors"
        :class="p.status === 'accepted' ? 'border-green-200' : p.status.startsWith('rejected') ? 'border-red-100' : 'border-gray-200'">

        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex-1 min-w-0">

            <!-- Título + badge status -->
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-semibold text-gray-800">{{ p.demand?.title }}</h3>
              <span :class="statusStyle(p.status).badge" class="text-xs px-2 py-0.5 rounded-full font-medium">
                {{ statusStyle(p.status).label }}
              </span>
            </div>

            <p class="text-sm text-gray-500 mt-0.5">{{ p.demand?.company?.trade_name }} · {{ p.demand?.service?.name }}</p>

            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-500">
              <span>📅 {{ formatDate(p.demand?.date) }}</span>
              <span>🕐 {{ p.demand?.start_time?.slice(0,5) }} – {{ p.demand?.end_time?.slice(0,5) }}</span>
              <span v-if="p.demand?.city">📍 {{ p.demand?.city }}/{{ p.demand?.state }}</span>
            </div>

            <p v-if="p.message" class="text-sm text-gray-400 italic mt-2 line-clamp-1">
              "{{ p.message }}"
            </p>

            <p class="text-xs text-gray-400 mt-1">Enviada em {{ formatDateTime(p.created_at) }}</p>
          </div>

          <!-- Ações -->
          <div class="flex flex-col gap-2 items-end shrink-0">

            <!-- Chat: aparece para propostas ativas -->
            <button v-if="podeChat(p.status)"
              @click="abrirChat(p)"
              class="flex items-center gap-1.5 px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
              Chat
            </button>

            <!-- Cancelar: apenas para pendentes -->
            <button v-if="['pending', 'pending_company_accept'].includes(p.status)"
              @click="cancelar(p)"
              class="text-xs text-red-400 hover:text-red-600 transition">
              Cancelar proposta
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Modal de Chat ───────────────────────────────────────────────── -->
    <div v-if="chatAberto" class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center justify-center z-50 p-0 sm:p-4">
      <div class="bg-white w-full sm:rounded-xl sm:max-w-lg flex flex-col" style="height: 90vh; max-height: 600px;">

        <!-- Header chat -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 shrink-0">
          <div>
            <p class="font-semibold text-gray-800 text-sm">{{ chatProposta?.demand?.company?.trade_name }}</p>
            <p class="text-xs text-gray-400">{{ chatProposta?.demand?.title }}</p>
          </div>
          <button @click="fecharChat" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Mensagens -->
        <div ref="messagesEl" class="flex-1 overflow-y-auto p-4 space-y-3">
          <div v-if="loadingMsg" class="flex justify-center py-4">
            <div class="w-5 h-5 border-2 border-orange-400 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="mensagens.length === 0" class="text-center text-gray-400 text-sm py-8">
            Nenhuma mensagem ainda. Inicie a conversa!
          </div>
          <div v-for="m in mensagens" :key="m.id"
            :class="m.sender_type === 'provider' ? 'flex justify-end' : 'flex justify-start'">
            <div :class="m.sender_type === 'provider'
              ? 'bg-orange-500 text-white'
              : 'bg-gray-100 text-gray-800'"
              class="max-w-xs rounded-2xl px-4 py-2 text-sm break-words">
              <p v-if="m.sender_type !== 'provider'" class="text-xs font-medium mb-0.5 text-gray-500">
                {{ m.sender_name ?? 'Empresa' }}
              </p>
              {{ m.body }}
              <p class="text-xs mt-1 opacity-60 text-right">{{ formatTime(m.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="px-4 py-3 border-t border-gray-100 shrink-0">
          <div class="flex gap-2">
            <input v-model="novaMensagem" type="text"
              placeholder="Digite sua mensagem..."
              class="flex-1 px-3 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
              @keydown.enter.prevent="enviarMensagem" />
            <button @click="enviarMensagem" :disabled="!novaMensagem.trim() || enviando"
              class="w-9 h-9 bg-orange-500 text-white rounded-full flex items-center justify-center hover:bg-orange-600 disabled:opacity-50 transition shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
            </button>
          </div>
        </div>

      </div>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  proposals: Array,
  filters:   Object,
})

// ── Filtro por status ──────────────────────────────────────────────────────────
const statusOpts = [
  { value: '',                      label: 'Todas' },
  { value: 'pending',               label: 'Pendente' },
  { value: 'pending_admin_approval',label: 'Aguardando aprovação' },
  { value: 'accepted',              label: 'Aceitas' },
  { value: 'rejected',              label: 'Recusadas' },
  { value: 'rejected_provider',     label: 'Canceladas' },
]

const filtroAtivo = ref(props.filters?.status ?? 'pending')

const propostasFiltradas = computed(() => {
  if (!filtroAtivo.value) return props.proposals
  return props.proposals.filter(p => p.status === filtroAtivo.value)
})

function setFiltro(v) {
  filtroAtivo.value = v
}

// ── Status styling ─────────────────────────────────────────────────────────────
function statusStyle(status) {
  const map = {
    pending:                { label: 'Aguardando empresa',   badge: 'bg-yellow-100 text-yellow-700' },
    pending_company_accept: { label: 'Para você aceitar',    badge: 'bg-blue-100 text-blue-700' },
    pending_admin_approval: { label: 'Aguardando aprovação', badge: 'bg-purple-100 text-purple-700' },
    accepted:               { label: 'Aceita ✓',             badge: 'bg-green-100 text-green-700' },
    rejected:               { label: 'Recusada',             badge: 'bg-red-100 text-red-600' },
    rejected_admin:         { label: 'Rejeitada pelo admin', badge: 'bg-red-100 text-red-600' },
    rejected_provider:      { label: 'Cancelada por você',   badge: 'bg-gray-100 text-gray-500' },
  }
  return map[status] ?? { label: status, badge: 'bg-gray-100 text-gray-500' }
}

function podeChat(status) {
  return ['pending', 'pending_company_accept', 'pending_admin_approval', 'accepted'].includes(status)
}

// ── Cancelar proposta ──────────────────────────────────────────────────────────
function cancelar(proposta) {
  if (!confirm('Cancelar esta proposta?')) return
  router.delete(route('prestador.propostas.cancelar', proposta.id))
}

// ── Chat ───────────────────────────────────────────────────────────────────────
const chatAberto    = ref(false)
const chatProposta  = ref(null)
const mensagens     = ref([])
const novaMensagem  = ref('')
const enviando      = ref(false)
const loadingMsg    = ref(false)
const messagesEl    = ref(null)
let echoChannel     = null

async function abrirChat(proposta) {
  chatProposta.value = proposta
  chatAberto.value   = true
  loadingMsg.value   = true
  mensagens.value    = []
  novaMensagem.value = ''

  // Carrega mensagens existentes
  try {
    const res = await fetch(route('prestador.propostas.mensagens', proposta.id), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    const data = await res.json()
    mensagens.value = data
    scrollBottom()
  } catch {
    // falha silenciosa
  } finally {
    loadingMsg.value = false
  }

  // Assina canal Reverb
  echoChannel = window.Echo
    .private(`proposal.${proposta.id}`)
    .listen('.message.sent', (e) => {
      // Evita duplicata (a própria mensagem enviada já foi adicionada localmente)
      if (!mensagens.value.find(m => m.id === e.id)) {
        mensagens.value.push(e)
        scrollBottom()
      }
    })
}

function fecharChat() {
  chatAberto.value = false
  if (echoChannel) {
    window.Echo.leave(`proposal.${chatProposta.value?.id}`)
    echoChannel = null
  }
}

async function enviarMensagem() {
  const body = novaMensagem.value.trim()
  if (!body || enviando.value) return

  enviando.value    = true
  novaMensagem.value = ''

  try {
    const res = await fetch(route('prestador.propostas.mensagens.enviar', chatProposta.value.id), {
      method:  'POST',
      headers: {
        'Content-Type':     'application/json',
        'Accept':           'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN':     document.querySelector('meta[name=csrf-token]')?.content ?? '',
      },
      body: JSON.stringify({ body }),
    })
    const msg = await res.json()
    // Adiciona localmente (não espera o Reverb re-emitir para si mesmo)
    mensagens.value.push({ ...msg, sender_type: 'provider' })
    scrollBottom()
  } catch {
    novaMensagem.value = body
  } finally {
    enviando.value = false
  }
}

async function scrollBottom() {
  await nextTick()
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
}

onUnmounted(() => {
  if (echoChannel) window.Echo.leave(`proposal.${chatProposta.value?.id}`)
})

// ── Helpers de data ────────────────────────────────────────────────────────────
function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.slice(0, 10).split('-')
  return new Date(Number(y), Number(m) - 1, Number(day)).toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit' })
}
function formatDateTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}
function formatTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}
</script>
