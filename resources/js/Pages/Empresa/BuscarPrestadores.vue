<template>
  <EmpresaLayout title="Buscar Prestadores">

    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
      <div class="flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-40">
          <label class="text-xs font-medium text-gray-500 block mb-1">Serviço</label>
          <select v-model="form.service_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
            <option value="">Todos os serviços</option>
            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <div class="flex-1 min-w-32">
          <label class="text-xs font-medium text-gray-500 block mb-1">Cidade</label>
          <input v-model="form.city" type="text" placeholder="Ex: São Paulo"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
        </div>

        <div class="w-28">
          <label class="text-xs font-medium text-gray-500 block mb-1">Estado</label>
          <select v-model="form.state"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
            <option value="">Todos</option>
            <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
          </select>
        </div>

        <!-- Toggle CNH -->
        <div class="flex items-center gap-2 py-2">
          <button type="button" @click="form.has_license = !form.has_license"
            class="shrink-0 w-10 h-5 rounded-full relative transition-colors duration-200 focus:outline-none"
            :class="form.has_license ? 'bg-teal-500' : 'bg-gray-200'">
            <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200"
              :class="form.has_license ? 'translate-x-5' : 'translate-x-0'" />
          </button>
          <span class="text-sm text-gray-600 whitespace-nowrap">Somente com CNH</span>
        </div>

        <div class="flex gap-2 shrink-0">
          <button type="button" @click="buscar"
            class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            Buscar
          </button>
          <button type="button" @click="limpar"
            class="px-4 py-2 text-sm text-gray-500 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Limpar
          </button>
        </div>
      </div>
    </div>

    <!-- Resultados -->
    <div v-if="providers.data.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="p in providers.data" :key="p.id"
        class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">

        <!-- Cabeçalho: foto + nome + avaliação -->
        <div class="flex items-start gap-3">
          <div class="rounded-full overflow-hidden border-2 border-teal-100 bg-teal-50" style="width:56px;height:56px;min-width:56px;flex-shrink:0;">
            <img v-if="p.profile_photo_path"
              :src="`/storage/${p.profile_photo_path}`"
              style="width:56px;height:56px;object-fit:cover;display:block;" />
            <div v-else class="flex items-center justify-center text-teal-300" style="width:56px;height:56px;">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800 text-sm truncate">{{ p.name }}</p>

            <!-- Avaliação -->
            <div class="flex items-center gap-1 mt-0.5">
              <div class="flex">
                <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5"
                  :class="i <= Math.round(p.ratings_avg_score || 0) ? 'text-amber-400' : 'text-gray-200'"
                  fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </div>
              <span class="text-xs text-gray-500">
                {{ p.ratings_avg_score ? Number(p.ratings_avg_score).toFixed(1) : 'Sem avaliações' }}
                <span v-if="p.ratings?.length" class="text-gray-400">({{ p.ratings.length }})</span>
              </span>
            </div>

            <!-- Localização -->
            <p v-if="p.city || p.state" class="text-xs text-gray-400 mt-1 flex items-center gap-1">
              <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ [p.city, p.state].filter(Boolean).join(' – ') }}
            </p>
          </div>

          <!-- Badge CNH -->
          <span v-if="p.has_license"
            class="shrink-0 text-xs bg-blue-50 text-blue-600 border border-blue-100 rounded-full px-2 py-0.5 font-medium">
            CNH
          </span>
        </div>

        <!-- Bio -->
        <p v-if="p.bio" class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ p.bio }}</p>

        <!-- Serviços -->
        <div v-if="p.services?.length" class="flex flex-wrap gap-1.5">
          <span v-for="s in p.services.slice(0, 4)" :key="s.id"
            class="text-xs bg-teal-50 text-teal-700 border border-teal-100 rounded-full px-2 py-1 font-medium">
            {{ s.name }}
          </span>
          <span v-if="p.services.length > 4"
            class="text-xs bg-gray-100 text-gray-500 rounded-full px-2.5 py-0.5">
            +{{ p.services.length - 4 }}
          </span>
        </div>
        <p v-else class="text-xs text-gray-300 italic">Nenhum serviço cadastrado</p>

        <!-- Botão Contratar -->
        <button type="button" @click="abrirModal(p)"
          class="mt-auto w-full py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition">
          Contratar
        </button>

      </div>
    </div>

    <!-- Sem resultados -->
    <div v-else class="bg-white rounded-xl border border-gray-200 py-16 text-center">
      <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <p class="text-gray-400 text-sm">Nenhum prestador encontrado para os filtros selecionados.</p>
      <button type="button" @click="limpar" class="mt-3 text-teal-600 text-sm font-medium hover:underline">
        Limpar filtros
      </button>
    </div>

    <!-- Paginação -->
    <div v-if="providers.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
      <button v-for="p in providers.links" :key="p.label"
        :disabled="!p.url"
        @click="p.url && router.visit(p.url)"
        class="px-3 py-1.5 rounded-lg text-sm transition"
        :class="p.active
          ? 'bg-teal-600 text-white font-medium'
          : p.url
            ? 'border border-gray-200 text-gray-600 hover:bg-gray-50'
            : 'border border-gray-100 text-gray-300 cursor-not-allowed'"
        v-html="p.label" />
    </div>

    <!-- ── Modal Contratar ────────────────────────────────────────────────────── -->
    <div v-if="modalAberto" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
          <div>
            <h2 class="font-bold text-gray-800">Contratar prestador</h2>
            <p class="text-sm text-gray-500">{{ selectedProvider?.name }}</p>
          </div>
          <button @click="fecharModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Toggle modo -->
        <div class="px-6 pt-4 shrink-0">
          <div class="flex bg-gray-100 rounded-lg p-1 gap-1">
            <button type="button" @click="modo = 'new'"
              class="flex-1 py-1.5 text-sm font-medium rounded-md transition"
              :class="modo === 'new' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
              Nova demanda
            </button>
            <button type="button" @click="modo = 'existing'"
              :disabled="!minhasDemandas.length"
              class="flex-1 py-1.5 text-sm font-medium rounded-md transition disabled:opacity-40 disabled:cursor-not-allowed"
              :class="modo === 'existing' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
              Demanda existente
            </button>
          </div>
        </div>

        <!-- Corpo do formulário -->
        <div class="overflow-y-auto flex-1 px-6 py-4">

          <!-- Erros -->
          <div v-if="Object.keys(contratarForm.errors).length"
            class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            <p v-for="(err, k) in contratarForm.errors" :key="k">{{ err }}</p>
          </div>

          <!-- Modo: Nova Demanda -->
          <div v-if="modo === 'new'" class="space-y-4">

            <!-- Serviço -->
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Serviço *</label>
              <select v-model="contratarForm.service_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white"
                :class="contratarForm.errors.service_id ? 'border-red-400' : ''">
                <option value="">Selecione o serviço</option>
                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>

            <!-- Título -->
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Título da demanda *</label>
              <input v-model="contratarForm.title" type="text" placeholder="Ex: Cozinheira para evento"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                :class="contratarForm.errors.title ? 'border-red-400' : ''" />
            </div>

            <!-- Data + horários -->
            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Data *</label>
                <input v-model="contratarForm.date" type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="contratarForm.errors.date ? 'border-red-400' : ''" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Início *</label>
                <input v-model="contratarForm.start_time" type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="contratarForm.errors.start_time ? 'border-red-400' : ''" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Fim *</label>
                <input v-model="contratarForm.end_time" type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="contratarForm.errors.end_time ? 'border-red-400' : ''" />
              </div>
            </div>

            <!-- CEP + cidade + estado -->
            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">CEP</label>
                <input v-model="contratarForm.cep" type="text" placeholder="00000-000" maxlength="9"
                  @input="onCepInput"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Cidade *</label>
                <input v-model="contratarForm.city" type="text" placeholder="São Paulo"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="contratarForm.errors.city ? 'border-red-400' : ''" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 block mb-1">Estado *</label>
                <select v-model="contratarForm.state"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white"
                  :class="contratarForm.errors.state ? 'border-red-400' : ''">
                  <option value="">UF</option>
                  <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
                </select>
              </div>
            </div>

            <!-- Descrição -->
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Descrição</label>
              <textarea v-model="contratarForm.description" rows="2"
                placeholder="Detalhes adicionais sobre o serviço..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none" />
            </div>

            <!-- Mensagem para prestador -->
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Mensagem para o prestador (opcional)</label>
              <textarea v-model="contratarForm.message" rows="2"
                placeholder="Ex: Preciso de alguém pontual e com experiência em eventos..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none" />
            </div>

          </div>

          <!-- Modo: Demanda Existente -->
          <div v-else class="space-y-4">

            <p class="text-sm text-gray-500">Selecione uma demanda aberta para vincular este prestador.</p>

            <div v-if="!minhasDemandas.length" class="text-sm text-gray-400 italic">
              Você não tem demandas abertas no momento.
            </div>

            <div v-else>
              <label class="text-xs font-medium text-gray-600 block mb-1">Demanda *</label>
              <select v-model="contratarForm.demand_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white"
                :class="contratarForm.errors.demand_id ? 'border-red-400' : ''">
                <option value="">Selecione a demanda</option>
                <option v-for="d in minhasDemandas" :key="d.id" :value="d.id">
                  {{ d.title }} — {{ d.service?.name }} ({{ formatDateShort(d.date) }})
                </option>
              </select>
            </div>

            <!-- Mensagem para prestador -->
            <div>
              <label class="text-xs font-medium text-gray-600 block mb-1">Mensagem para o prestador (opcional)</label>
              <textarea v-model="contratarForm.message" rows="2"
                placeholder="Ex: Você está disponível nesta data?"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none" />
            </div>

          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex gap-3 justify-end shrink-0">
          <button type="button" @click="fecharModal"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Cancelar
          </button>
          <button type="button" @click="submitContratar"
            :disabled="contratarForm.processing"
            class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
            {{ contratarForm.processing ? 'Enviando...' : 'Enviar convite' }}
          </button>
        </div>

      </div>
    </div>

  </EmpresaLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const props = defineProps({
  providers:      Object,
  services:       Array,
  filters:        Object,
  minhasDemandas: Array,
})

// ── Filtros de busca ───────────────────────────────────────────────────────────
const form = reactive({
  service_id:  props.filters?.service_id  ?? '',
  city:        props.filters?.city        ?? '',
  state:       props.filters?.state       ?? '',
  has_license: props.filters?.has_license ?? false,
})

function buscar() {
  router.get(route('empresa.prestadores.index'), {
    service_id:  form.service_id  || undefined,
    city:        form.city        || undefined,
    state:       form.state       || undefined,
    has_license: form.has_license || undefined,
  }, { preserveState: true, replace: true })
}

function limpar() {
  form.service_id  = ''
  form.city        = ''
  form.state       = ''
  form.has_license = false
  router.get(route('empresa.prestadores.index'), {}, { replace: true })
}

// ── Modal Contratar ────────────────────────────────────────────────────────────
const modalAberto      = ref(false)
const selectedProvider = ref(null)
const modo             = ref('new')

const contratarForm = useForm({
  mode:        'new',
  // Nova demanda
  service_id:  '',
  title:       '',
  date:        '',
  start_time:  '',
  end_time:    '',
  cep:         '',
  city:        '',
  state:       '',
  description: '',
  // Demanda existente
  demand_id:   '',
  // Comum
  message:     '',
})

function abrirModal(provider) {
  selectedProvider.value = provider
  modo.value = 'new'
  contratarForm.reset()
  contratarForm.clearErrors()
  modalAberto.value = true
}

function fecharModal() {
  modalAberto.value = false
  selectedProvider.value = null
}

function submitContratar() {
  contratarForm.mode = modo.value

  contratarForm.post(route('empresa.prestadores.contratar', selectedProvider.value.id), {
    preserveScroll: true,
    onSuccess: () => { fecharModal() },
  })
}

// ── CEP auto-fill ──────────────────────────────────────────────────────────────
let cepTimer = null

function onCepInput(e) {
  let v = e.target.value.replace(/\D/g, '')
  if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5, 8)
  contratarForm.cep = v

  clearTimeout(cepTimer)
  const digits = v.replace(/\D/g, '')
  if (digits.length === 8) {
    cepTimer = setTimeout(() => buscarCep(digits), 400)
  }
}

async function buscarCep(cep) {
  try {
    const res = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    if (!res.ok) return
    const data = await res.json()
    if (data.city)  contratarForm.city  = data.city
    if (data.state) contratarForm.state = data.state
  } catch {
    // falha silenciosa
  }
}

// ── Helpers ────────────────────────────────────────────────────────────────────
function formatDateShort(d) {
  if (!d) return ''
  const [y, m, day] = d.slice(0, 10).split('-')
  return `${day}/${m}/${y}`
}

const ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']
</script>
