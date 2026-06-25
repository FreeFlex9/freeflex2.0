<template>
  <div class="min-h-screen bg-gradient-to-br from-teal-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-teal-600">FreeFlex</h1>
        <p class="text-gray-500 mt-1 text-sm">Área da Empresa</p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form @submit.prevent="submit" class="space-y-4">

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">E-mail ou CNPJ</label>
            <input v-model="form.email" type="text" autocomplete="email"
              class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              :class="form.errors.email ? 'border-red-400' : 'border-gray-300'"
              placeholder="empresa@email.com ou 00.000.000/0001-00" />
            <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">Senha</label>
            <input v-model="form.password" type="password" autocomplete="current-password"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2.5 rounded-lg transition-colors disabled:opacity-60 text-sm mt-2">
            {{ form.processing ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          Não tem conta?
          <Link :href="route('empresa.register')" class="text-teal-600 font-medium hover:underline">Cadastre sua empresa</Link>
        </p>
      </div>

      <p class="mt-4 text-center text-xs text-gray-400">
        É prestador?
        <Link :href="route('prestador.login')" class="underline">Acesse aqui</Link>
      </p>

    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({ email: '', password: '' })
function submit() {
  form.post(route('empresa.login.submit'), { onFinish: () => form.reset('password') })
}
</script>
