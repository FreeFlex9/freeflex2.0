<template>
  <div class="min-h-screen bg-gradient-to-br from-orange-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <div class="text-center mb-6">
        <div class="flex justify-center items-center gap-2">
          <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-8 w-auto rounded-full" />
          <span class="text-xl font-bold text-orange-500">FreeFlex</span>
        </div>
        <p class="text-gray-500 mt-2 text-sm">Cadastro de Prestador</p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form @submit.prevent="submit" class="space-y-6">

          <!-- Dados Pessoais -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Dados Pessoais</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="flex flex-col gap-1 sm:col-span-2">
                <label class="text-sm font-medium text-gray-700">Nome Completo <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"
                  placeholder="Seu nome completo" />
                <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">CPF <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input v-model="form.cpf" type="text" maxlength="14"
                    class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 pr-10"
                    :class="form.errors.cpf ? 'border-red-400' : cpfOk ? 'border-green-400' : 'border-gray-300'"
                    placeholder="000.000.000-00" @input="maskCpf" @blur="checkCpf" />
                  <span v-if="loadingCpf" class="absolute right-3 top-2.5 text-xs text-gray-400">verificando...</span>
                  <svg v-else-if="cpfOk" class="absolute right-3 top-2.5 w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
                <p v-if="form.errors.cpf" class="text-xs text-red-600">{{ form.errors.cpf }}</p>
                <p v-else-if="cpfOk" class="text-xs text-green-600">CPF encontrado na Receita Federal ✓</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Data de Nascimento</label>
                <input v-model="form.birth_date" type="date"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.email ? 'border-red-400' : 'border-gray-300'"
                  placeholder="seu@email.com" />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Telefone <span class="text-red-500">*</span></label>
                <input v-model="form.phone" type="text" maxlength="15"
                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  :class="form.errors.phone ? 'border-red-400' : 'border-gray-300'"
                  placeholder="(00) 00000-0000" @input="maskPhone" />
                <p v-if="form.errors.phone" class="text-xs text-red-600">{{ form.errors.phone }}</p>
              </div>

            </div>
          </div>

          <!-- Habilitação -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Habilitação (CNH)</p>
            <div class="space-y-3">
              <label class="flex items-center gap-3 cursor-pointer">
                <input v-model="form.has_license" type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-teal-500 focus:ring-teal-500" />
                <span class="text-sm text-gray-700">Possuo CNH</span>
              </label>
              <div v-if="form.has_license" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-1">
                <label class="flex items-center gap-3 cursor-pointer sm:col-span-2">
                  <input v-model="form.is_digital_license" type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-teal-500 focus:ring-teal-500" />
                  <span class="text-sm text-gray-700">CNH Digital</span>
                </label>
                <div class="flex flex-col gap-1 sm:col-span-2">
                  <label class="text-sm font-medium text-gray-700">Número da CNH</label>
                  <input v-model="form.license_number" type="text"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                    placeholder="Número do documento" />
                </div>
              </div>
            </div>
          </div>

          <!-- Documentação obrigatória -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Documentação</p>
            <p class="text-xs text-gray-500 mb-3">
              Envie fotos ou PDF (máx. 5 MB cada) do documento correspondente para que seu cadastro possa ser analisado.
            </p>

            <div v-if="!form.has_license" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <FileField label="RG - Frente" required v-model="form.rg_front" :error="form.errors.rg_front" />
              <FileField label="RG - Verso" required v-model="form.rg_back" :error="form.errors.rg_back" />
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <FileField label="CNH - Frente" required v-model="form.license_front" :error="form.errors.license_front" />
              <FileField v-if="!form.is_digital_license" label="CNH - Verso" required
                v-model="form.license_back" :error="form.errors.license_back" />
            </div>
          </div>

          <!-- Bio -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Sobre Você</p>
            <textarea v-model="form.bio" rows="3"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"
              placeholder="Descreva sua experiência, habilidades e disponibilidade... (opcional)" />
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

          <p class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg p-3">
            Após o cadastro, seu perfil passará por uma análise. Você poderá enviar propostas após a aprovação do administrador.
          </p>

          <button type="submit" :disabled="form.processing"
            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-colors disabled:opacity-60 text-sm">
            {{ form.processing ? 'Cadastrando...' : 'Criar Conta' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          Já tem conta?
          <Link :href="route('prestador.login')" class="text-orange-500 font-medium hover:underline">Entrar</Link>
        </p>
      </div>

      <p class="mt-4 text-center text-xs text-gray-400">
        É empresa?
        <Link :href="route('empresa.login')" class="underline">Acesse aqui</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { validateCpf, validatePhone } from '@/utils/validators'
import FileField from '@/Components/FileField.vue'

const form = useForm({
  name: '', cpf: '', email: '', phone: '', birth_date: '',
  has_license: false, is_digital_license: false, license_number: '',
  bio: '', password: '', password_confirmation: '',
  rg_front: null, rg_back: null, license_front: null, license_back: null,
})

const loadingCpf = ref(false)
const cpfOk = ref(false)

function maskCpf(e) {
  cpfOk.value = false
  let v = e.target.value.replace(/\D/g, '').slice(0, 11)
  v = v.replace(/(\d{3})(\d)/, '$1.$2')
  v = v.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
  v = v.replace(/\.(\d{3})(\d)/, '.$1-$2')
  form.cpf = v
}

async function checkCpf() {
  cpfOk.value = false
  if (!validateCpf(form.cpf)) {
    form.setError('cpf', 'CPF inválido. Verifique os dígitos.')
    return
  }
  form.clearErrors('cpf')
  loadingCpf.value = true
  try {
    const digits = form.cpf.replace(/\D/g, '')
    const res = await fetch(`/api/validar-cpf/${digits}`)
    const data = await res.json()
    if (data.valido === true) {
      cpfOk.value = true
    } else if (data.valido === false) {
      form.setError('cpf', data.mensagem)
    }
    // valido === null significa API indisponível — não bloqueia
  } catch {
    // rede fora — não bloqueia
  }
  loadingCpf.value = false
}

function maskPhone(e) {
  let v = e.target.value.replace(/\D/g, '').slice(0, 11)
  if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d)/, '($1) $2-$3')
  else if (v.length > 2) v = v.replace(/^(\d{2})(\d)/, '($1) $2')
  form.phone = v
}

function submit() {
  if (!validateCpf(form.cpf)) {
    form.setError('cpf', 'CPF inválido. Verifique os dígitos.')
    return
  }
  if (!validatePhone(form.phone)) {
    form.setError('phone', 'Celular inválido. Use DDD + 9 dígitos.')
    return
  }
  if (form.has_license) {
    if (!form.license_front) return form.setError('license_front', 'Envie a foto da CNH.')
    if (!form.is_digital_license && !form.license_back) return form.setError('license_back', 'Envie a foto do verso da CNH.')
  } else {
    if (!form.rg_front) return form.setError('rg_front', 'Envie a foto da frente do RG.')
    if (!form.rg_back) return form.setError('rg_back', 'Envie a foto do verso do RG.')
  }
  form.post(route('prestador.register.submit'))
}
</script>
