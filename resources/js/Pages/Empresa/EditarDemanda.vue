<template>
  <EmpresaLayout title="Editar Demanda">
    <div class="max-w-2xl">
      <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form @submit.prevent="submit" class="space-y-5">

          <!-- SERVIÇO & TÍTULO -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Sobre o serviço</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="sm:col-span-2 flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Título da demanda <span class="text-red-400">*</span></label>
                <input v-model="form.title" type="text"
                  class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.title ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Tipo de serviço <span class="text-red-400">*</span></label>
                <select v-model="form.service_id"
                  class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white"
                  :class="form.errors.service_id ? 'border-red-400' : 'border-gray-300'">
                  <option value="">Selecione...</option>
                  <option v-for="s in services" :key="s.id" :value="s.id">
                    {{ s.name }}{{ s.requires_license ? ' 🪪' : '' }}
                  </option>
                </select>
                <p v-if="form.errors.service_id" class="text-xs text-red-500">{{ form.errors.service_id }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Nº de prestadores <span class="text-red-400">*</span></label>
                <input v-model.number="form.slots_needed" type="number" min="1" max="50"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

            </div>
          </div>

          <!-- DATA & HORÁRIO -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Data e horário</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Data <span class="text-red-400">*</span></label>
                <input v-model="form.date" type="date"
                  class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.date ? 'border-red-400' : 'border-gray-300'" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Início <span class="text-red-400">*</span></label>
                <input v-model="form.start_time" type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Fim <span class="text-red-400">*</span></label>
                <input v-model="form.end_time" type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <p v-if="duracaoHoras" class="sm:col-span-3 text-xs text-teal-600">
                ⏱ Duração: {{ duracaoHoras }}
              </p>

            </div>
          </div>

          <!-- LOCAL -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Local do serviço</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">CEP</label>
                <input v-model="form.zip_code" type="text" maxlength="9"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  @blur="fetchCep" />
              </div>

              <div class="flex flex-col gap-1 sm:col-span-2">
                <label class="text-xs font-medium text-gray-500">Logradouro</label>
                <input v-model="form.street" type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Número</label>
                <input v-model="form.number" type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Complemento</label>
                <input v-model="form.complement" type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Bairro</label>
                <input v-model="form.neighborhood" type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Cidade <span class="text-red-400">*</span></label>
                <input v-model="form.city" type="text"
                  class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.city ? 'border-red-400' : 'border-gray-300'" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Estado <span class="text-red-400">*</span></label>
                <select v-model="form.state"
                  class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white"
                  :class="form.errors.state ? 'border-red-400' : 'border-gray-300'">
                  <option value="">Selecione</option>
                  <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
                </select>
              </div>

            </div>
          </div>

          <!-- DESCRIÇÃO -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Descrição / observações</label>
            <textarea v-model="form.description" rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none" />
          </div>

          <!-- Botões -->
          <div class="flex gap-3 pt-2">
            <Link :href="route('empresa.demandas.show', demand.id)"
              class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
              Cancelar
            </Link>
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-60">
              {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </EmpresaLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

const props = defineProps({ demand: Object, services: Array })

const form = useForm({
  title:        props.demand.title,
  service_id:   props.demand.service_id,
  slots_needed: props.demand.slots_needed,
  date:         props.demand.date?.slice(0, 10) ?? '',
  start_time:   props.demand.start_time?.slice(0, 5) ?? '',
  end_time:     props.demand.end_time?.slice(0, 5) ?? '',
  zip_code:     props.demand.zip_code     ?? '',
  street:       props.demand.street       ?? '',
  number:       props.demand.number       ?? '',
  complement:   props.demand.complement   ?? '',
  neighborhood: props.demand.neighborhood ?? '',
  city:         props.demand.city         ?? '',
  state:        props.demand.state        ?? '',
  description:  props.demand.description  ?? '',
})

const duracaoHoras = computed(() => {
  if (!form.start_time || !form.end_time) return null
  const [h1, m1] = form.start_time.split(':').map(Number)
  const [h2, m2] = form.end_time.split(':').map(Number)
  const min = (h2 * 60 + m2) - (h1 * 60 + m1)
  if (min <= 0) return null
  const h = Math.floor(min / 60)
  const m = min % 60
  return h > 0 ? (m > 0 ? `${h}h ${m}min` : `${h}h`) : `${m}min`
})

async function fetchCep() {
  const cep = form.zip_code.replace(/\D/g, '')
  if (cep.length !== 8) return
  try {
    const res = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    if (res.ok) {
      const d = await res.json()
      form.street       = d.street       || form.street
      form.neighborhood = d.neighborhood || form.neighborhood
      form.city         = d.city         || form.city
      form.state        = d.state        || form.state
    }
  } catch {}
}

function submit() {
  form.put(route('empresa.demandas.update', props.demand.id))
}

const ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']
</script>
