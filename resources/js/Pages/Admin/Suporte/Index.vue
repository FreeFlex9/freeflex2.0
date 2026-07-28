<template>
  <AdminLayout title="Suporte" :pendentes="pendentes">
    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Prestador</label>
        <input v-model="filtros.prestador" @input="filtrar" type="text" placeholder="Nome prestador..."
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Empresa</label>
        <input v-model="filtros.empresa" @input="filtrar" type="text" placeholder="Nome empresa..."
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Demanda</label>
        <input v-model="filtros.demanda" @input="filtrar" type="text" placeholder="Título da demanda..."
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>
      <button @click="limpar" class="text-sm text-gray-500 underline self-end mb-0.5">Limpar</button>
    </div>

    <div v-if="propostas.data.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
      Nenhuma proposta encontrada.
    </div>

    <div v-else class="space-y-3">
      <div v-for="prop in propostas.data" :key="prop.id" class="bg-white rounded-xl shadow p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800">#{{ prop.id }} — {{ prop.demand?.title ?? '—' }}</p>
            <div class="mt-1 text-sm text-gray-500 space-y-0.5">
              <p>Prestador: <span class="text-gray-700">{{ prop.provider?.name ?? '—' }}</span></p>
              <p>Empresa: <span class="text-gray-700">{{ prop.demand?.company?.trade_name ?? '—' }}</span></p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <button @click="abrirChat(prop, 'provider')"
              class="px-3 py-1.5 bg-orange-50 text-orange-700 text-sm rounded-lg hover:bg-orange-100 transition-colors">
              Chat com Prestador
            </button>
            <button @click="abrirChat(prop, 'company')"
              class="px-3 py-1.5 bg-teal-50 text-teal-700 text-sm rounded-lg hover:bg-teal-100 transition-colors">
              Chat com Empresa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginação -->
    <div v-if="propostas.last_page > 1" class="mt-4 flex gap-2 justify-center">
      <Link v-for="link in propostas.links" :key="link.label" :href="link.url ?? '#'"
        :class="['px-3 py-1 text-sm rounded border', link.active ? 'bg-green-500 text-white border-green-500' : 'border-gray-300 text-gray-600 hover:bg-gray-50']"
        v-html="link.label" />
    </div>

    <!-- Modal de chat -->
    <div v-if="chatProposta" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black bg-opacity-40">
      <div class="bg-white w-full sm:max-w-lg sm:rounded-2xl flex flex-col shadow-2xl" style="height: 90vh; max-height: 600px;">

        <!-- Header chat -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-100">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800 text-sm truncate">
              {{ chatThreadType === 'provider' ? chatProposta.provider?.name : chatProposta.demand?.company?.trade_name }}
            </p>
            <p class="text-xs text-gray-400 truncate">
              {{ chatThreadType === 'provider' ? 'Conversa com o Prestador' : 'Conversa com a Empresa' }} — {{ chatProposta.demand?.title }}
            </p>
          </div>
          <button @click="fecharChat" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Mensagens -->
        <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-2">
          <div v-if="carregandoMensagens" class="flex justify-center py-8">
            <svg class="w-6 h-6 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12H4z"/>
            </svg>
          </div>
          <div v-else-if="mensagens.length === 0" class="text-center text-gray-400 text-sm py-8">
            Nenhuma mensagem ainda.
          </div>
          <div v-else v-for="m in mensagens" :key="m.id"
            class="flex" :class="m.sender_type === 'admin' ? 'justify-end' : 'justify-start'">
            <div :class="m.sender_type === 'admin' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-800'"
              class="max-w-xs px-3 py-2 rounded-xl text-sm">
              <p v-if="m.sender_type !== 'admin'" class="text-xs font-medium mb-0.5 text-gray-500">
                {{ m.sender_type === 'provider' ? (chatProposta.provider?.name ?? 'Prestador') : (chatProposta.demand?.company?.trade_name ?? 'Empresa') }}
              </p>
              {{ m.body }}
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-gray-100 flex gap-2">
          <input v-model="novaMensagem" type="text" placeholder="Digite sua mensagem..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
            @keydown.enter.prevent="enviarMensagem" />
          <button @click="enviarMensagem" :disabled="!novaMensagem.trim()"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition disabled:opacity-50">
            Enviar
          </button>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ propostas: Object, filters: Object })
const pendentes = { empresas: 0, prestadores: 0, propostas: 0 }
const filtros = ref({
  prestador: props.filters?.prestador ?? '',
  empresa:   props.filters?.empresa ?? '',
  demanda:   props.filters?.demanda ?? '',
})

let timer = null
function filtrar() {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get(route('admin.suporte.index'), filtros.value, { preserveState: true, replace: true })
  }, 400)
}
function limpar() { filtros.value = { prestador: '', empresa: '', demanda: '' }; filtrar() }

// ── Chat ───────────────────────────────────────────────────────────────────────
const chatProposta        = ref(null)
const chatThreadType      = ref(null)
const mensagens           = ref([])
const novaMensagem        = ref('')
const carregandoMensagens = ref(false)
const chatBox             = ref(null)
let   echoChannel         = null

async function abrirChat(proposta, threadType) {
  chatProposta.value = proposta
  chatThreadType.value = threadType
  carregandoMensagens.value = true
  mensagens.value = []
  novaMensagem.value = ''

  const res = await fetch(route('admin.suporte.mensagens', [proposta.id, threadType]), {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
  })
  if (res.ok) mensagens.value = await res.json()
  carregandoMensagens.value = false

  await nextTick()
  scrollBaixo()

  if (window.Echo) {
    echoChannel = window.Echo.private(`proposal.${proposta.id}.${threadType}`)
      .listen('.message.sent', (e) => {
        if (!mensagens.value.find(m => m.id === e.id)) {
          mensagens.value.push(e)
          nextTick(scrollBaixo)
        }
      })
  }
}

function fecharChat() {
  if (echoChannel && chatProposta.value) {
    window.Echo?.leave(`proposal.${chatProposta.value.id}.${chatThreadType.value}`)
  }
  echoChannel = null
  chatProposta.value = null
  chatThreadType.value = null
}

async function enviarMensagem() {
  const body = novaMensagem.value.trim()
  if (!body) return
  novaMensagem.value = ''

  const res = await fetch(route('admin.suporte.mensagens.enviar', [chatProposta.value.id, chatThreadType.value]), {
    method: 'POST',
    headers: {
      'Content-Type':     'application/json',
      'Accept':           'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN':     document.querySelector('meta[name=csrf-token]')?.content ?? '',
    },
    body: JSON.stringify({ body }),
  })
  if (res.ok) {
    const msg = await res.json()
    if (!mensagens.value.find(m => m.id === msg.id)) {
      mensagens.value.push(msg)
      await nextTick()
      scrollBaixo()
    }
  }
}

function scrollBaixo() {
  if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
}

onUnmounted(() => {
  if (echoChannel && chatProposta.value) {
    window.Echo?.leave(`proposal.${chatProposta.value.id}.${chatThreadType.value}`)
  }
})
</script>
