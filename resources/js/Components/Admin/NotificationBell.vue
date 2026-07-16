<template>
  <div ref="root" class="relative">
    <button
      class="relative text-gray-500 hover:text-gray-700"
      @click="open = !open"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] leading-none rounded-full min-w-[16px] h-4 flex items-center justify-center px-1"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div
      v-if="open"
      class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-700">Notificações</span>
        <button
          v-if="unreadCount > 0"
          class="text-xs text-green-600 hover:text-green-700"
          @click="marcarTodasLidas"
        >
          Marcar todas como lidas
        </button>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <p v-if="!items.length" class="px-4 py-6 text-sm text-gray-400 text-center">
          Nenhuma notificação por aqui.
        </p>
        <button
          v-for="item in items"
          :key="item.id"
          class="w-full text-left px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 flex gap-2"
          :class="!item.read_at && 'bg-green-50/50'"
          @click="marcarLida(item)"
        >
          <span
            class="mt-1 w-2 h-2 rounded-full shrink-0"
            :class="item.read_at ? 'bg-transparent' : 'bg-green-500'"
          />
          <span class="flex-1">
            <span class="block text-sm text-gray-700">{{ item.data.mensagem }}</span>
            <span class="block text-xs text-gray-400 mt-1">{{ formatarData(item.created_at) }}</span>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const open = ref(false)
const root = ref(null)

const items = computed(() => page.props.notifications?.items ?? [])
const unreadCount = computed(() => page.props.notifications?.unread_count ?? 0)

function marcarLida(item) {
  if (!item.read_at) {
    router.post(route('admin.notificacoes.marcar-lida', item.id), {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['notifications'],
    })
  }
}

function marcarTodasLidas() {
  router.post(route('admin.notificacoes.marcar-todas-lidas'), {}, {
    preserveScroll: true,
    preserveState: true,
    only: ['notifications'],
  })
}

function formatarData(iso) {
  return new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}

function handleClickOutside(event) {
  if (open.value && root.value && !root.value.contains(event.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>
