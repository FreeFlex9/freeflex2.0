<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-green-400">FreeFlex</h1>
        <p class="text-gray-400 mt-1">Painel Administrativo</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Entrar como administrador</h2>

        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
              :class="{ 'border-red-400': form.errors.email }"
              placeholder="admin@freeflex.com.br"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
              :class="{ 'border-red-400': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 rounded-lg transition-colors disabled:opacity-60"
          >
            {{ form.processing ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('admin.login.submit'), {
    onFinish: () => form.reset('password'),
  })
}
</script>
