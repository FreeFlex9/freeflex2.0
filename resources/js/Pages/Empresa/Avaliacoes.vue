<template>
  <EmpresaLayout title="Avaliações">

    <div v-if="!items.length" class="text-center py-20 bg-white rounded-xl border border-gray-200">
      <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
      </svg>
      <p class="text-gray-500 text-sm">Nenhum serviço concluído para avaliar.</p>
      <p class="text-xs text-gray-400 mt-1">Após a data do serviço, os prestadores confirmados aparecerão aqui.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="item in items" :key="item.schedule_id"
        class="bg-white rounded-xl border border-gray-200 p-5">

        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
          <div>
            <p class="font-semibold text-gray-800">{{ item.demand_title }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ item.service_name }} · {{ formatDate(item.demand_date) }}</p>
          </div>
          <span v-if="item.rating" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
            Avaliado
          </span>
        </div>

        <!-- Prestador -->
        <div class="flex flex-wrap items-start gap-4">
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <img v-if="item.provider_photo"
              :src="'/storage/' + item.provider_photo"
              class="w-10 h-10 rounded-full object-cover border border-gray-200 shrink-0" />
            <div v-else
              class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
              <span class="text-orange-500 font-bold">{{ item.provider_name?.[0] }}</span>
            </div>
            <div class="min-w-0">
              <p class="font-medium text-gray-800 text-sm">{{ item.provider_name }}</p>
              <p class="text-xs text-gray-400">Prestador confirmado</p>
            </div>
          </div>

          <!-- Avaliação existente ou formulário -->
          <div class="w-full sm:w-auto">

            <!-- Já avaliado -->
            <div v-if="item.rating">
              <div class="flex gap-0.5 mb-1">
                <svg v-for="i in 5" :key="i" class="w-5 h-5"
                  :class="i <= item.rating.score ? 'text-amber-400' : 'text-gray-200'"
                  fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </div>
              <p v-if="item.rating.comment" class="text-sm text-gray-600 italic">"{{ item.rating.comment }}"</p>
              <button @click="editarAvaliacao(item)"
                class="text-xs text-teal-600 hover:underline mt-1">
                Editar avaliação
              </button>
            </div>

            <!-- Formulário de avaliação -->
            <div v-else>
              <p class="text-xs text-gray-500 mb-1.5">Avalie o prestador:</p>
              <div class="flex gap-1 mb-2">
                <button v-for="i in 5" :key="i"
                  @click="setScore(item, i)"
                  class="transition-transform hover:scale-110">
                  <svg class="w-7 h-7"
                    :class="(scores[item.schedule_id] ?? 0) >= i ? 'text-amber-400' : 'text-gray-200'"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </button>
              </div>
              <textarea v-model="comments[item.schedule_id]" rows="2"
                placeholder="Comentário (opcional)..."
                class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none mb-2" />
              <button @click="enviarAvaliacao(item)"
                :disabled="!scores[item.schedule_id] || enviando[item.schedule_id]"
                class="px-4 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-lg transition disabled:opacity-50">
                {{ enviando[item.schedule_id] ? 'Enviando...' : 'Enviar avaliação' }}
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Modal editar avaliação -->
    <div v-if="itemEditando" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-40">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl">
        <h3 class="font-semibold text-gray-800 mb-1">Editar avaliação</h3>
        <p class="text-xs text-gray-500 mb-4">{{ itemEditando.provider_name }} · {{ itemEditando.demand_title }}</p>

        <div class="flex gap-1 mb-3 justify-center">
          <button v-for="i in 5" :key="i" @click="editScore = i" class="transition-transform hover:scale-110">
            <svg class="w-8 h-8" :class="editScore >= i ? 'text-amber-400' : 'text-gray-200'"
              fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </button>
        </div>

        <textarea v-model="editComment" rows="3"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none mb-4" />

        <div class="flex gap-3">
          <button @click="itemEditando = null"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            Cancelar
          </button>
          <button @click="salvarEdicao"
            class="flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg">
            Salvar
          </button>
        </div>
      </div>
    </div>

  </EmpresaLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const props = defineProps({ items: Array })

const scores   = reactive({})
const comments = reactive({})
const enviando = reactive({})

function setScore(item, val) {
  scores[item.schedule_id] = val
}

function enviarAvaliacao(item) {
  if (!scores[item.schedule_id]) return
  enviando[item.schedule_id] = true
  router.post(route('empresa.avaliacoes.store'), {
    demand_id:   item.demand_id,
    provider_id: item.provider_id,
    score:       scores[item.schedule_id],
    comment:     comments[item.schedule_id] ?? null,
  }, {
    onFinish: () => { enviando[item.schedule_id] = false },
  })
}

const itemEditando = ref(null)
const editScore    = ref(0)
const editComment  = ref('')

function editarAvaliacao(item) {
  itemEditando.value = item
  editScore.value    = item.rating?.score ?? 0
  editComment.value  = item.rating?.comment ?? ''
}

function salvarEdicao() {
  router.post(route('empresa.avaliacoes.store'), {
    demand_id:   itemEditando.value.demand_id,
    provider_id: itemEditando.value.provider_id,
    score:       editScore.value,
    comment:     editComment.value || null,
  }, {
    onSuccess: () => { itemEditando.value = null },
  })
}

function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.slice(0, 10).split('-')
  return `${day}/${m}/${y}`
}
</script>
