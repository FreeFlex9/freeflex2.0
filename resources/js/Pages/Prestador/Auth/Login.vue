<template>
  <div class="min-h-screen bg-gradient-to-br from-orange-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-orange-500">FreeFlex</h1>
        <p class="text-gray-500 mt-1 text-sm">Área do Prestador</p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Entrar</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail ou CPF</label>
            <input v-model="form.email" type="text" autocomplete="email"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              :class="{ 'border-red-400': form.errors.email }"
              placeholder="seu@email.com ou 000.000.000-00" />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
            <input v-model="form.password" type="password" autocomplete="current-password"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>
          <button type="submit" :disabled="form.processing"
            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-colors disabled:opacity-60 text-sm">
            {{ form.processing ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          Não tem conta?
          <Link :href="route('prestador.register')" class="text-orange-500 font-medium hover:underline">Criar conta</Link>
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
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({ email: '', password: '', remember: false })
function submit() {
  form.post(route('prestador.login.submit'), { onFinish: () => form.reset('password') })
}
</script>
