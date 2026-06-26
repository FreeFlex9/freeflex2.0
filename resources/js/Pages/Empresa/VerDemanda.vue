<template>
  <EmpresaLayout :title="demand.title">

    <div class="max-w-4xl space-y-5">

      <!-- Cabeçalho da demanda -->
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">
                {{ demand.service?.name }}
              </span>
              <span v-if="demand.service?.requires_license" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                🪪 Exige CNH
              </span>
              <span :class="statusClass(demand.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                {{ statusLabel(demand.status) }}
              </span>
            </div>
            <h2 class="font-bold text-gray-800 text-lg">{{ demand.title }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">
              {{ formatDate(demand.date) }} · {{ demand.start_time?.slice(0,5) }} – {{ demand.end_time?.slice(0,5) }}
            </p>
            <p class="text-sm text-gray-500 mt-0.5" v-if="demand.city">
              📍 {{ [demand.street, demand.number, demand.neighborhood, demand.city + '/' + demand.state].filter(Boolean).join(', ') }}
            </p>
          </div>

          <div class="text-right flex flex-col items-end gap-2">
            <div>
              <p class="text-xs text-gray-400">Vagas confirmadas</p>
              <p class="text-2xl font-bold text-teal-600">{{ demand.slots_confirmed }}<span class="text-gray-400 text-base font-normal">/{{ demand.slots_needed }}</span></p>
            </div>
            <Link v-if="demand.status === 'open'" :href="route('empresa.demandas.edit', demand.id)"
              class="text-xs px-3 py-1.5 border border-teal-600 text-teal-600 hover:bg-teal-50 rounded-lg transition">
              Editar demanda
            </Link>
          </div>
        </div>

        <p v-if="demand.description" class="text-sm text-gray-600 mt-3 border-t border-gray-100 pt-3">
          {{ demand.description }}
        </p>
      </div>

      <!-- Propostas -->
      <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
          Propostas recebidas
          <span class="ml-1 text-xs font-normal text-gray-400">({{ proposals.length }})</span>
        </h3>

        <div v-if="proposals.length" class="space-y-3">
          <div v-for="p in proposals" :key="p.id"
            class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition">

            <div class="flex flex-wrap items-start gap-3">
              <!-- Foto -->
              <img v-if="p.provider?.profile_photo_path"
                :src="'/storage/' + p.provider.profile_photo_path"
                class="w-12 h-12 rounded-full object-cover border border-gray-200 shrink-0" />
              <div v-else
                class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                <span class="text-orange-500 font-bold text-lg">{{ p.provider?.name?.[0] }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="font-semibold text-gray-800 text-sm">{{ p.provider?.name }}</span>
                  <span v-if="p.provider?.has_license" class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">CNH</span>
                  <span :class="propostaStatusClass(p.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ propostaStatusLabel(p.status) }}
                  </span>
                </div>
                <p class="text-xs text-gray-400">{{ p.provider?.email }}</p>
                <p v-if="p.message" class="text-sm text-gray-600 mt-1 italic">"{{ p.message }}"</p>
                <p class="text-xs text-gray-400 mt-1">Proposta enviada {{ formatDatetime(p.created_at) }}</p>
              </div>

              <!-- Ações -->
              <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Chat -->
                <button @click="abrirChat(p)"
                  class="flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 text-gray-600 hover:bg-gray-50 text-xs font-medium rounded-lg transition">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                  </svg>
                  Chat
                </button>

                <!-- Aceitar -->
                <button v-if="p.status === 'pending'"
                  @click="acao('aceitar', p)"
                  class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-lg transition">
                  Aceitar
                </button>

                <!-- Rejeitar -->
                <button v-if="p.status === 'pending'"
                  @click="acao('rejeitar', p)"
                  class="px-3 py-1.5 border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium rounded-lg transition">
                  Rejeitar
                </button>
              </div>
            </div>

          </div>
        </div>

        <div v-else class="text-center py-12 bg-white border border-gray-200 rounded-xl">
          <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p class="text-gray-400 text-sm">Nenhuma proposta recebida ainda.</p>
        </div>
      </div>

    </div>

    <!-- Modal de chat -->
    <div v-if="chatProposta" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black bg-opacity-40">
      <div class="bg-white w-full sm:max-w-lg sm:rounded-2xl flex flex-col shadow-2xl" style="height: 90vh; max-height: 600px;">

        <!-- Header chat -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-100">
          <img v-if="chatProposta.provider?.profile_photo_path"
            :src="'/storage/' + chatProposta.provider.profile_photo_path"
            class="w-9 h-9 rounded-full object-cover" />
          <div v-else class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center">
            <span class="text-orange-500 font-bold">{{ chatProposta.provider?.name?.[0] }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800 text-sm truncate">{{ chatProposta.provider?.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ demand.title }}</p>
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
          <div v-else v-for="m in mensagens" :key="m.id"
            class="flex" :class="m.sender_type === 'company' ? 'justify-end' : 'justify-start'">
            <div :class="m.sender_type === 'company' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-800'"
              class="max-w-xs px-3 py-2 rounded-xl text-sm">
              {{ m.body }}
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-gray-100 flex gap-2">
          <input v-model="novaMensagem" type="text" placeholder="Digite sua mensagem..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            @keydown.enter.prevent="enviarMensagem" />
          <button @click="enviarMensagem" :disabled="!novaMensagem.trim()"
            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm rounded-lg transition disabled:opacity-50">
            Enviar
          </button>
        </div>

      </div>
    </div>

  </EmpresaLayout>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const props = defineProps({
  demand:    Object,
  proposals: Array,
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
  return { open:'Aberta', partially_scheduled:'Parcialmente agendada', scheduled:'Agendada', cancelled:'Cancelada' }[status] ?? status
}
function propostaStatusClass(status) {
  return {
    pending:                'bg-yellow-100 text-yellow-700',
    pending_admin_approval: 'bg-purple-100 text-purple-700',
    accepted:               'bg-green-100 text-green-700',
    rejected:               'bg-red-100 text-red-500',
    rejected_provider:      'bg-gray-100 text-gray-500',
  }[status] ?? 'bg-gray-100 text-gray-500'
}
function propostaStatusLabel(status) {
  return {
    pending:                'Aguardando decisão',
    pending_admin_approval: 'Aguardando admin',
    accepted:               'Aceita',
    rejected:               'Rejeitada',
    rejected_provider:      'Cancelada pelo prestador',
  }[status] ?? status
}
function formatDate(s) {
  if (!s) return ''
  const [y, m, d] = s.slice(0, 10).split('-')
  return `${d}/${m}/${y}`
}
function formatDatetime(s) {
  if (!s) return ''
  const dt = new Date(s)
  return dt.toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' })
}

function acao(tipo, proposta) {
  const routeName = tipo === 'aceitar' ? 'empresa.propostas.aceitar' : 'empresa.propostas.rejeitar'
  router.post(route(routeName, proposta.id))
}

// Chat
const chatProposta        = ref(null)
const mensagens           = ref([])
const novaMensagem        = ref('')
const carregandoMensagens = ref(false)
const chatBox             = ref(null)
let   echoChannel         = null

async function abrirChat(proposta) {
  chatProposta.value = proposta
  carregandoMensagens.value = true
  mensagens.value = []
  novaMensagem.value = ''

  const res = await fetch(route('empresa.propostas.mensagens', proposta.id), {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
  })
  if (res.ok) mensagens.value = await res.json()
  carregandoMensagens.value = false

  await nextTick()
  scrollBaixo()

  if (window.Echo) {
    echoChannel = window.Echo.private(`proposal.${proposta.id}`)
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
    window.Echo?.leave(`proposal.${chatProposta.value.id}`)
  }
  echoChannel = null
  chatProposta.value = null
}

async function enviarMensagem() {
  const body = novaMensagem.value.trim()
  if (!body) return
  novaMensagem.value = ''

  const res = await fetch(route('empresa.propostas.mensagens.enviar', chatProposta.value.id), {
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
    window.Echo?.leave(`proposal.${chatProposta.value.id}`)
  }
})
</script>
