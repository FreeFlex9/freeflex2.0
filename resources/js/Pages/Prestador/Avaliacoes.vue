<template>
  <PrestadorLayout title="Avaliações">

    <!-- Média geral -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
      <div v-if="media !== null" class="flex flex-wrap items-center gap-6">
        <div class="text-center">
          <p class="text-5xl font-bold text-orange-500">{{ media }}</p>
          <div class="flex justify-center gap-0.5 mt-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5"
              :class="i <= Math.round(media) ? 'text-amber-400' : 'text-gray-200'"
              fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-sm text-gray-500 mt-1">{{ total }} avaliação{{ total !== 1 ? 'ões' : '' }}</p>
        </div>

        <!-- Barras de distribuição -->
        <div class="flex-1 min-w-48 space-y-1">
          <div v-for="n in [5,4,3,2,1]" :key="n" class="flex items-center gap-2">
            <span class="text-xs text-gray-500 w-3">{{ n }}</span>
            <svg class="w-3 h-3 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <div class="flex-1 bg-gray-100 rounded-full h-2">
              <div class="bg-amber-400 h-2 rounded-full transition-all"
                :style="{ width: total ? (contagem(n) / total * 100) + '%' : '0%' }" />
            </div>
            <span class="text-xs text-gray-400 w-4 text-right">{{ contagem(n) }}</span>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-4">
        <p class="text-gray-400 text-sm">Você ainda não recebeu nenhuma avaliação.</p>
        <p class="text-xs text-gray-400 mt-1">As empresas poderão avaliar após a data do serviço.</p>
      </div>
    </div>

    <!-- Lista de avaliações -->
    <div v-if="ratings.length" class="space-y-3">
      <div v-for="r in ratings" :key="r.id"
        class="bg-white rounded-xl border border-gray-200 p-4">

        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-800 text-sm">{{ r.company_name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ r.demand_title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(r.demand_date) }} · avaliado em {{ r.created_at }}</p>
          </div>

          <!-- Estrelas -->
          <div class="flex gap-0.5 shrink-0">
            <svg v-for="i in 5" :key="i" class="w-4 h-4"
              :class="i <= r.score ? 'text-amber-400' : 'text-gray-200'"
              fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
        </div>

        <p v-if="r.comment" class="text-sm text-gray-600 mt-2 italic border-t border-gray-100 pt-2">
          "{{ r.comment }}"
        </p>
      </div>
    </div>

  </PrestadorLayout>
</template>

<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({
  ratings: Array,
  media:   Number,
  total:   Number,
})

function contagem(score) {
  return props.ratings.filter(r => r.score === score).length
}

function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.slice(0, 10).split('-')
  return `${day}/${m}/${y}`
}
</script>
