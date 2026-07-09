<template>
  <PrestadorLayout title="Check-in / Check-out">

    <!-- Navegação de data -->
    <div class="flex items-center gap-3 mb-5">
      <button @click="navegar(-1)"
        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <h2 class="text-base font-bold text-gray-800 capitalize min-w-56 text-center">{{ dataLabel }}</h2>
      <button @click="navegar(1)"
        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
      <button v-if="!isHoje" @click="irParaHoje"
        class="text-sm text-orange-600 hover:text-orange-700 font-medium">Hoje</button>
    </div>

    <!-- Erro de geolocalização -->
    <div v-if="geoError" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
      {{ geoError }}
    </div>

    <div v-if="!schedules.length" class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
      Nenhum agendamento para este dia.
    </div>

    <div v-else class="space-y-4">
      <div v-for="s in schedules" :key="s.id" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="font-semibold text-gray-800 text-sm truncate">{{ s.service_name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ s.company_name }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ s.start_time }} – {{ s.end_time }}</p>
            <p v-if="s.address" class="text-xs text-gray-400 mt-1">📍 {{ s.address }}</p>
            <p v-if="!s.has_location" class="text-xs text-amber-600 mt-1">
              Endereço sem geolocalização cadastrada — check-in não valida distância.
            </p>
          </div>
          <span :class="statusBadgeClass(s)" class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0">
            {{ statusLabel(s) }}
          </span>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-3">
          <template v-if="!s.check_in_at">
            <button v-if="isHoje" :disabled="loadingId === s.id"
              @click="fazerCheckin(s)"
              class="px-3 py-1.5 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600 transition disabled:opacity-50">
              {{ loadingId === s.id ? 'Obtendo localização…' : 'Fazer check-in' }}
            </button>
            <span v-else class="text-xs text-gray-400">Check-in disponível no dia do agendamento.</span>
          </template>

          <template v-else>
            <span class="text-xs text-green-700 bg-green-50 px-2 py-1 rounded-lg">
              Check-in às {{ s.check_in_at }}
              <span v-if="s.check_in_distance_m !== null">({{ s.check_in_distance_m }}m do local)</span>
            </span>

            <button v-if="!s.check_out_at" :disabled="loadingId === s.id"
              @click="fazerCheckout(s)"
              class="px-3 py-1.5 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 transition disabled:opacity-50">
              {{ loadingId === s.id ? 'Obtendo localização…' : 'Fazer check-out' }}
            </button>

            <span v-else class="text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">
              Check-out às {{ s.check_out_at }}
              <span v-if="s.check_out_distance_m !== null">({{ s.check_out_distance_m }}m do local)</span>
            </span>
          </template>
        </div>
      </div>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  schedules: Array,
  data:      String,
})

const loadingId = ref(null)
const geoError = ref('')

const hojeStr = (() => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
})()

const isHoje = computed(() => props.data === hojeStr)

const dataLabel = computed(() => {
  const [y, m, d] = props.data.split('-').map(Number)
  return new Date(y, m - 1, d).toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' })
})

function navegar(delta) {
  const [y, m, d] = props.data.split('-').map(Number)
  const dt = new Date(y, m - 1, d)
  dt.setDate(dt.getDate() + delta)
  const novaData = `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')}`
  router.get(route('prestador.ponto.index'), { data: novaData }, { preserveState: false })
}

function irParaHoje() {
  router.get(route('prestador.ponto.index'), { data: hojeStr }, { preserveState: false })
}

function statusLabel(s) {
  if (s.check_out_at) return 'Concluído'
  if (s.check_in_at) return 'Em andamento'
  return 'Agendado'
}

function statusBadgeClass(s) {
  if (s.check_out_at) return 'bg-green-100 text-green-700'
  if (s.check_in_at) return 'bg-blue-100 text-blue-700'
  return 'bg-orange-100 text-orange-700'
}

function obterLocalizacao() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Seu navegador não suporta geolocalização.'))
      return
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => resolve({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
      (err) => {
        const mensagens = {
          1: 'Permissão de localização negada. Habilite o acesso à localização para fazer o check-in.',
          2: 'Não foi possível obter sua localização. Tente novamente.',
          3: 'Tempo esgotado ao obter localização. Tente novamente.',
        }
        reject(new Error(mensagens[err.code] ?? 'Erro ao obter localização.'))
      },
      { enableHighAccuracy: true, timeout: 15000 }
    )
  })
}

async function fazerCheckin(s) {
  geoError.value = ''
  loadingId.value = s.id
  try {
    const coords = await obterLocalizacao()
    router.post(route('prestador.ponto.checkin', s.id), coords, {
      preserveScroll: true,
      onFinish: () => { loadingId.value = null },
    })
  } catch (e) {
    geoError.value = e.message
    loadingId.value = null
  }
}

async function fazerCheckout(s) {
  geoError.value = ''
  loadingId.value = s.id
  try {
    const coords = await obterLocalizacao()
    router.post(route('prestador.ponto.checkout', s.id), coords, {
      preserveScroll: true,
      onFinish: () => { loadingId.value = null },
    })
  } catch (e) {
    geoError.value = e.message
    loadingId.value = null
  }
}
</script>
