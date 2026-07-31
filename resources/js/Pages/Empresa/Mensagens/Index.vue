<template>
  <EmpresaLayout title="Mensagens">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden" style="height: calc(100vh - 140px); min-height: 420px;">
      <div class="flex h-full">

        <!-- Lista de conversas -->
        <div class="w-full lg:w-80 shrink-0 border-r border-gray-100 dark:border-gray-800 flex flex-col"
          :class="selecionada ? 'hidden lg:flex' : 'flex'">
          <div class="p-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Conversas</h2>
          </div>
          <div class="flex-1 overflow-y-auto">
            <p v-if="conversas.length === 0" class="text-center text-gray-400 text-sm py-8 px-4">
              Nenhuma conversa ainda. Publique uma demanda para receber propostas.
            </p>
            <button v-for="c in conversas" :key="c.demand_id" @click="selecionar(c)"
              class="w-full text-left px-4 py-3 border-b border-gray-50 dark:border-gray-800/60 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors"
              :class="selecionada?.demand_id === c.demand_id && 'bg-teal-50 dark:bg-teal-500/10'">
              <div class="flex items-start justify-between gap-2">
                <p class="font-medium text-sm text-gray-800 dark:text-gray-100 truncate">{{ c.demand_title ?? 'Demanda' }}</p>
                <span v-if="c.unread_count > 0" class="shrink-0 text-xs bg-red-500 text-white rounded-full px-1.5 py-0.5 leading-none">
                  {{ c.unread_count > 9 ? '9+' : c.unread_count }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">{{ c.last_message_body ?? 'Sem mensagens ainda' }}</p>
            </button>
          </div>
        </div>

        <!-- Painel de chat -->
        <div class="flex-1 min-w-0" :class="selecionada ? 'flex' : 'hidden lg:flex'">
          <ChatPanel v-if="selecionada" ref="panel"
            :messages="thread.messages.value" :loading="thread.loading.value"
            :disabled="thread.sending.value" current-role="company"
            :title="selecionada.demand_title ?? 'Demanda'"
            other-label="Suporte FreeFlex"
            show-back @back="fechar" @send="enviar" />
          <div v-else class="flex-1 flex items-center justify-center text-sm text-gray-400">
            Selecione uma conversa para ver as mensagens.
          </div>
        </div>

      </div>
    </div>
  </EmpresaLayout>
</template>

<script setup>
import { ref } from 'vue'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'
import ChatPanel from '@/Components/Chat/ChatPanel.vue'
import { useChatThread } from '@/composables/useChatThread.js'

const props = defineProps({ conversas: { type: Array, default: () => [] } })

const selecionada = ref(null)
let thread = useChatThread({ fetchUrl: null, sendUrl: null, channelName: null })

function selecionar(c) {
  if (selecionada.value) fechar()

  selecionada.value = c
  thread = useChatThread({
    fetchUrl:    route('empresa.demandas.mensagens', c.demand_id),
    sendUrl:     route('empresa.demandas.mensagens.enviar', c.demand_id),
    channelName: `chat.${c.demand_id}.company`,
  })
  thread.load().then(() => { c.unread_count = 0 })
  thread.subscribe()
}

function fechar() {
  thread.unsubscribe()
  selecionada.value = null
}

function enviar(body) {
  thread.send(body)
}
</script>
