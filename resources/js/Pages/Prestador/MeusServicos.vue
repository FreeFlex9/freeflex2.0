<template>
  <PrestadorLayout title="Meus Serviços">

    <div class="max-w-2xl">
      <p class="text-sm text-gray-500 mb-5">
        Selecione os serviços que você oferece. As demandas correspondentes aparecerão para você em Buscar Demandas.
      </p>

      <div class="space-y-3">
        <div v-for="s in services" :key="s.id"
          class="bg-white border rounded-xl p-4 flex items-center justify-between gap-4 transition"
          :class="meuSet.has(s.id) ? 'border-orange-300 bg-orange-50' : 'border-gray-200'">

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-medium text-gray-800 text-sm">{{ s.name }}</span>
              <span v-if="s.requires_license"
                class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">
                🪪 Exige CNH
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">
              Repasse: <span class="font-semibold text-green-600">R$ {{ formatMoney(s.provider_rate) }}/h</span>
              <span class="mx-1.5 text-gray-300">·</span>
              Valor empresa: R$ {{ formatMoney(s.hourly_rate) }}/h
            </p>
          </div>

          <!-- Toggle -->
          <button @click="toggle(s)"
            :disabled="s.requires_license && !providerHasLicense"
            :title="s.requires_license && !providerHasLicense ? 'Você não possui CNH cadastrada' : ''"
            class="shrink-0 w-12 h-6 rounded-full relative transition-colors duration-200 focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed"
            :class="meuSet.has(s.id) ? 'bg-orange-500' : 'bg-gray-200'">
            <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
              :class="meuSet.has(s.id) ? 'translate-x-6' : 'translate-x-0'" />
          </button>

        </div>
      </div>

      <p v-if="!services.length" class="text-center text-gray-400 text-sm py-10">
        Nenhum serviço cadastrado pelo administrador ainda.
      </p>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  services: Array,
  myIds:    Array,
})

const page = usePage()
const providerHasLicense = page.props.auth?.provider?.has_license ?? false

const meuSet = ref(new Set(props.myIds))

function toggle(service) {
  if (service.requires_license && !providerHasLicense) return

  // Optimistic UI
  if (meuSet.value.has(service.id)) {
    meuSet.value.delete(service.id)
  } else {
    meuSet.value.add(service.id)
  }
  meuSet.value = new Set(meuSet.value) // trigger reactivity

  router.post(route('prestador.servicos.toggle'), { service_id: service.id }, {
    preserveState: true,
    onError: () => {
      // Reverte se der erro
      if (meuSet.value.has(service.id)) {
        meuSet.value.delete(service.id)
      } else {
        meuSet.value.add(service.id)
      }
      meuSet.value = new Set(meuSet.value)
    },
  })
}

function formatMoney(v) {
  return Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 })
}
</script>
