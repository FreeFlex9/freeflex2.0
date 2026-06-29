<template>
  <EmpresaLayout title="Meu Perfil">

    <!-- Aviso -->
    <div v-if="!company.cnpj_card_path && company.status === 'pending'"
      class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div>
        <strong>Envie o Cartão CNPJ para ser aprovada.</strong>
        O administrador precisa verificar o documento antes de liberar sua conta.
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Info da empresa -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-4">Dados da Empresa</h3>
        <form @submit.prevent="saveInfo" class="grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Nome Fantasia <span class="text-red-400">*</span></label>
            <input v-model="infoForm.trade_name" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Razão Social</label>
            <input v-model="infoForm.legal_name" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Telefone</label>
            <input v-model="infoForm.phone" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              placeholder="(00) 00000-0000" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">CEP</label>
            <input v-model="infoForm.zip_code" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              placeholder="00000-000" @blur="fetchAddress" />
          </div>

          <div class="flex flex-col gap-1 sm:col-span-2">
            <label class="text-xs font-medium text-gray-500">Logradouro</label>
            <input v-model="infoForm.street" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Número</label>
            <input v-model="infoForm.number" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Bairro</label>
            <input v-model="infoForm.neighborhood" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Cidade</label>
            <input v-model="infoForm.city" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Estado</label>
            <select v-model="infoForm.state"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
              <option value="">Selecione</option>
              <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
            </select>
          </div>

          <div class="sm:col-span-2">
            <button type="submit" :disabled="infoForm.processing"
              class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition disabled:opacity-60">
              Salvar alterações
            </button>
          </div>
        </form>
      </div>

      <!-- Documentos + Senha -->
      <div class="space-y-4">

        <!-- Alterar senha -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 text-sm mb-4">Alterar Senha</h3>
          <form @submit.prevent="savePassword" class="space-y-3">
            <div>
              <label class="text-xs font-medium text-gray-500">Senha atual</label>
              <input v-model="passForm.current_password" type="password"
                class="w-full mt-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                :class="passForm.errors.current_password ? 'border-red-400' : 'border-gray-300'" />
              <p v-if="passForm.errors.current_password" class="text-xs text-red-500 mt-1">{{ passForm.errors.current_password }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Nova senha</label>
              <input v-model="passForm.password" type="password"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                placeholder="Mínimo 8 caracteres" />
              <p v-if="passForm.errors.password" class="text-xs text-red-500 mt-1">{{ passForm.errors.password }}</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Confirmar nova senha</label>
              <input v-model="passForm.password_confirmation" type="password"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
            </div>
            <button type="submit" :disabled="passForm.processing"
              class="w-full bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium py-2 rounded-lg transition disabled:opacity-60">
              Alterar senha
            </button>
          </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 text-sm mb-1">Documentos</h3>
          <p class="text-xs text-gray-400 mb-4">JPG, PNG ou PDF · máx. 5 MB</p>

          <!-- Cartão CNPJ -->
          <div class="p-3 rounded-lg border"
            :class="company.cnpj_card_path ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'">
            <div class="flex items-center gap-2 mb-2">
              <svg v-if="company.cnpj_card_path" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span class="text-sm font-medium text-gray-700">Cartão CNPJ <span class="text-red-400 text-xs">*</span></span>
            </div>
            <p class="text-xs mb-3" :class="company.cnpj_card_path ? 'text-green-600' : 'text-amber-600'">
              {{ company.cnpj_card_path ? 'Enviado ✓' : 'Obrigatório para aprovação' }}
            </p>
            <div class="flex gap-2">
              <a v-if="company.cnpj_card_path" :href="`/storage/${company.cnpj_card_path}`" target="_blank"
                class="text-xs text-blue-500 hover:underline">Ver arquivo</a>
              <label class="cursor-pointer flex-1">
                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf" @change="uploadCnpjCard" :disabled="uploading" />
                <span class="block text-center text-xs font-medium text-teal-600 border border-teal-400 rounded px-2 py-1.5 hover:bg-teal-50 transition"
                  :class="uploading ? 'opacity-50 cursor-not-allowed' : ''">
                  {{ uploading ? 'Enviando...' : company.cnpj_card_path ? 'Substituir' : 'Enviar' }}
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Configuração do Sistema -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 text-sm mb-4">Configuração do Sistema</h3>

          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                :class="isDark ? 'bg-indigo-100 text-indigo-600' : 'bg-amber-100 text-amber-600'">
                <svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-700">Modo escuro</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  {{ isDark ? 'Interface escura ativada' : 'Interface clara ativada' }}
                </p>
              </div>
            </div>
            <button type="button" @click="toggle"
              class="shrink-0 w-12 h-6 rounded-full relative transition-colors duration-200 focus:outline-none"
              :class="isDark ? 'bg-indigo-500' : 'bg-gray-200'">
              <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
                :class="isDark ? 'translate-x-6' : 'translate-x-0'" />
            </button>
          </div>
        </div>

      </div>

    </div>
  </EmpresaLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'
import { useDarkMode } from '@/composables/useDarkMode.js'

const { isDark, toggle } = useDarkMode()

const props = defineProps({ company: Object })

const infoForm = useForm({
  trade_name:   props.company.trade_name   ?? '',
  legal_name:   props.company.legal_name   ?? '',
  phone:        props.company.phone        ?? '',
  zip_code:     props.company.zip_code     ?? '',
  street:       props.company.street       ?? '',
  number:       props.company.number       ?? '',
  complement:   props.company.complement   ?? '',
  neighborhood: props.company.neighborhood ?? '',
  city:         props.company.city         ?? '',
  state:        props.company.state        ?? '',
})

function saveInfo() {
  infoForm.put(route('empresa.perfil.update'))
}

async function fetchAddress() {
  const cep = infoForm.zip_code.replace(/\D/g, '')
  if (cep.length !== 8) return
  try {
    const res = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    if (res.ok) {
      const data = await res.json()
      infoForm.street       = data.street       || infoForm.street
      infoForm.neighborhood = data.neighborhood || infoForm.neighborhood
      infoForm.city         = data.city         || infoForm.city
      infoForm.state        = data.state        || infoForm.state
    }
  } catch {}
}

const passForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

function savePassword() {
  passForm.put(route('empresa.perfil.senha'), {
    onSuccess: () => passForm.reset(),
  })
}

const uploading = ref(false)

function uploadCnpjCard(event) {
  const file = event.target?.files?.[0]
  if (!file) return
  uploading.value = true
  const form = new FormData()
  form.append('arquivo', file)
  router.post(route('empresa.perfil.documento'), form, {
    forceFormData: true,
    onFinish: () => { uploading.value = false },
  })
}

const ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']
</script>
