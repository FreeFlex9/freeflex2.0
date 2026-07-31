<template>
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="flex items-center gap-3 p-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
      <button v-if="showBack" @click="$emit('back')" class="lg:hidden text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm truncate">{{ title }}</p>
        <p v-if="subtitle" class="text-xs text-gray-400 truncate">{{ subtitle }}</p>
      </div>
    </div>

    <!-- Mensagens -->
    <div ref="scrollEl" class="flex-1 overflow-y-auto p-4 space-y-2">
      <div v-if="loading" class="flex justify-center py-8">
        <svg class="w-6 h-6 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12H4z"/>
        </svg>
      </div>
      <div v-else-if="messages.length === 0" class="text-center text-gray-400 text-sm py-8">
        Nenhuma mensagem ainda. Envie a primeira.
      </div>
      <div v-else v-for="m in messages" :key="m.id"
        class="flex" :class="m.sender_type === currentRole ? 'justify-end' : 'justify-start'">
        <div :class="m.sender_type === currentRole
          ? 'bg-orange-500 text-white dark:bg-orange-600'
          : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'"
          class="max-w-xs sm:max-w-sm px-3 py-2 rounded-xl text-sm">
          <p v-if="m.sender_type !== currentRole" class="text-xs font-medium mb-0.5 text-gray-500 dark:text-gray-400">
            {{ senderLabel(m) }}
          </p>
          {{ m.body }}
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="p-3 border-t border-gray-100 dark:border-gray-800 flex gap-2 shrink-0">
      <input v-model="draft" type="text" :disabled="disabled" placeholder="Digite sua mensagem..."
        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 disabled:opacity-50"
        @keydown.enter.prevent="submit" />
      <button @click="submit" :disabled="disabled || !draft.trim()"
        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded-lg transition disabled:opacity-50">
        Enviar
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  messages:    { type: Array, default: () => [] },
  loading:     { type: Boolean, default: false },
  disabled:    { type: Boolean, default: false },
  currentRole: { type: String, required: true }, // 'provider' | 'company' | 'admin'
  title:       { type: String, default: '' },
  subtitle:    { type: String, default: '' },
  showBack:    { type: Boolean, default: false },
  otherLabel:  { type: String, default: 'Suporte FreeFlex' },
})

const emit = defineEmits(['send', 'back'])

const draft = ref('')
const scrollEl = ref(null)

function senderLabel(message) {
  return message.sender_type === 'admin' ? 'Suporte FreeFlex' : props.otherLabel
}

function submit() {
  const body = draft.value.trim()
  if (!body) return
  draft.value = ''
  emit('send', body)
}

async function scrollBottom() {
  await nextTick()
  if (scrollEl.value) scrollEl.value.scrollTop = scrollEl.value.scrollHeight
}

watch(() => props.messages.length, scrollBottom)
watch(() => props.loading, (v) => { if (!v) scrollBottom() })

defineExpose({ scrollBottom })
</script>
