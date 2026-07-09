<template>
  <div class="min-h-screen bg-gradient-to-br from-teal-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <div class="text-center mb-6">
        <div class="flex justify-center items-center gap-2">
          <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-8 w-auto rounded-full" />
          <span class="text-xl font-bold text-teal-600">FreeFlex</span>
        </div>
        <p class="text-gray-500 mt-1 text-sm">Cadastro de Empresa</p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form @submit.prevent="submit" class="space-y-5">

          <!-- Dados da Empresa -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Dados da Empresa</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Nome Fantasia <span class="text-red-500">*</span></label>
                <input v-model="form.trade_name" type="text"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.trade_name ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Nome Fantasia" />
                <p v-if="form.errors.trade_name" class="text-xs text-red-600">{{ form.errors.trade_name }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Razão Social</label>
                <input v-model="form.legal_name" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Razão Social (opcional)" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">CNPJ <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input v-model="form.cnpj" type="text" maxlength="18"
                    class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 pr-10"
                    :class="form.errors.cnpj ? 'border-red-400' : cnpjOk ? 'border-green-400' : 'border-gray-300'"
                    placeholder="00.000.000/0001-00" @input="maskCnpj" @blur="checkCnpj" />
                  <span v-if="loadingCnpj" class="absolute right-3 top-2.5 text-xs text-gray-400">verificando...</span>
                  <svg v-else-if="cnpjOk" class="absolute right-3 top-2.5 w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
                <p v-if="form.errors.cnpj" class="text-xs text-red-600">{{ form.errors.cnpj }}</p>
                <p v-else-if="cnpjOk" class="text-xs text-green-600">CNPJ ativo na Receita Federal ✓</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Telefone <span class="text-red-500">*</span></label>
                <input v-model="form.phone" type="text" maxlength="15"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.phone ? 'border-red-400' : 'border-gray-300'"
                  placeholder="(00) 00000-0000" @input="maskPhone" />
                <p v-if="form.errors.phone" class="text-xs text-red-600">{{ form.errors.phone }}</p>
              </div>

              <div class="flex flex-col gap-1 sm:col-span-2">
                <label class="text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.email ? 'border-red-400' : 'border-gray-300'"
                  placeholder="empresa@email.com" />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
              </div>

            </div>
          </div>

          <!-- Endereço -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Endereço</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">CEP</label>
                <div class="relative">
                  <input v-model="form.zip_code" type="text" maxlength="9"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                    placeholder="00000-000" @input="maskCep" @blur="fetchAddress" />
                  <span v-if="loadingCep" class="absolute right-3 top-2.5 text-xs text-gray-400">buscando...</span>
                </div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Estado</label>
                <select v-model="form.state"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                  <option value="">Selecione</option>
                  <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
                </select>
              </div>

              <div class="flex flex-col gap-1 sm:col-span-2">
                <label class="text-sm font-medium text-gray-700">Logradouro</label>
                <input v-model="form.street" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Rua / Avenida" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Número</label>
                <input v-model="form.number" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Nº" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Bairro</label>
                <input v-model="form.neighborhood" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Bairro" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Cidade</label>
                <input v-model="form.city" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Cidade" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Complemento</label>
                <input v-model="form.complement" type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Apto, sala, bloco... (opcional)" />
              </div>

            </div>
          </div>

          <!-- Documentação obrigatória -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Documentação</p>
            <p class="text-xs text-gray-500 mb-3">
              Envie uma foto ou PDF (máx. 5 MB) do Cartão CNPJ para que o cadastro possa ser analisado.
            </p>
            <FileField label="Cartão CNPJ" required v-model="form.cnpj_card" :error="form.errors.cnpj_card" />
          </div>

          <!-- Acesso -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Acesso</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Senha <span class="text-red-500">*</span></label>
                <input v-model="form.password" type="password"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.password ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Mínimo 8 caracteres" />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Confirmar Senha <span class="text-red-500">*</span></label>
                <input v-model="form.password_confirmation" type="password"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.password_confirmation ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Repita a senha" />
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
              </div>

            </div>
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2.5 rounded-lg transition-colors disabled:opacity-60 text-sm">
            {{ form.processing ? 'Cadastrando...' : 'Criar Conta' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          Já tem conta?
          <Link :href="route('empresa.login')" class="text-teal-600 font-medium hover:underline">Entrar</Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { validateCnpj, validatePhone } from '@/utils/validators'
import FileField from '@/Components/FileField.vue'

const form = useForm({
  trade_name: '', legal_name: '', cnpj: '', email: '', phone: '',
  zip_code: '', street: '', number: '', complement: '', neighborhood: '', city: '', state: '',
  password: '', password_confirmation: '', cnpj_card: null,
})

const loadingCep = ref(false)
const loadingCnpj = ref(false)
const cnpjOk = ref(false)

function maskCnpj(e) {
  let v = e.target.value.replace(/\D/g, '').slice(0, 14)
  v = v.replace(/^(\d{2})(\d)/, '$1.$2')
  v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
  v = v.replace(/\.(\d{3})(\d)/, '.$1/$2')
  v = v.replace(/(\d{4})(\d)/, '$1-$2')
  form.cnpj = v
}

function maskPhone(e) {
  let v = e.target.value.replace(/\D/g, '').slice(0, 11)
  if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d)/, '($1) $2-$3')
  else if (v.length > 2) v = v.replace(/^(\d{2})(\d)/, '($1) $2')
  form.phone = v
}

function maskCep(e) {
  let v = e.target.value.replace(/\D/g, '').slice(0, 8)
  if (v.length > 5) v = v.replace(/^(\d{5})(\d)/, '$1-$2')
  form.zip_code = v
}

async function checkCnpj() {
  cnpjOk.value = false
  if (!validateCnpj(form.cnpj)) {
    form.setError('cnpj', 'CNPJ inválido. Verifique os dígitos.')
    return
  }
  form.clearErrors('cnpj')
  loadingCnpj.value = true
  try {
    const digits = form.cnpj.replace(/\D/g, '')
    const res = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${digits}`)
    if (!res.ok) {
      form.setError('cnpj', 'CNPJ não encontrado na Receita Federal.')
    } else {
      const data = await res.json()
      const situacao = data.descricao_situacao_cadastral ?? ''
      if (situacao !== 'ATIVA') {
        form.setError('cnpj', `CNPJ com situação "${situacao}". Apenas CNPJs ativos são aceitos.`)
      } else {
        cnpjOk.value = true
        // Preenche razão social automaticamente se estiver vazio
        if (!form.legal_name && data.razao_social) {
          form.legal_name = data.razao_social
        }
        if (!form.trade_name && data.nome_fantasia) {
          form.trade_name = data.nome_fantasia
        }
      }
    }
  } catch {
    // API indisponível — backend valida
  }
  loadingCnpj.value = false
}

async function fetchAddress() {
  const cep = form.zip_code.replace(/\D/g, '')
  if (cep.length !== 8) return
  loadingCep.value = true
  try {
    const res = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    if (res.ok) {
      const data = await res.json()
      form.street       = data.street       || form.street
      form.neighborhood = data.neighborhood || form.neighborhood
      form.city         = data.city         || form.city
      form.state        = data.state        || form.state
    }
  } catch {}
  loadingCep.value = false
}

function submit() {
  if (!validateCnpj(form.cnpj)) {
    form.setError('cnpj', 'CNPJ inválido. Verifique os dígitos.')
    return
  }
  if (!validatePhone(form.phone)) {
    form.setError('phone', 'Celular inválido. Use DDD + 9 dígitos.')
    return
  }
  if (!form.cnpj_card) {
    form.setError('cnpj_card', 'Envie o Cartão CNPJ.')
    return
  }
  form.post(route('empresa.register.submit'))
}

const ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']
</script>
