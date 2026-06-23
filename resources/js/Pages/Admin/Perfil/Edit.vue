<template>
  <AdminLayout title="Meu Perfil" :pendentes="pendentes">
    <div class="max-w-lg space-y-6">
      <!-- Alterar senha -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Alterar Senha</h2>

        <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 text-sm">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="salvar" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha atual</label>
            <input v-model="form.senha_atual" type="password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              :class="{ 'border-red-400': form.errors.senha_atual }" />
            <p v-if="form.errors.senha_atual" class="text-xs text-red-600 mt-0.5">{{ form.errors.senha_atual }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
            <input v-model="form.nova_senha" type="password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              :class="{ 'border-red-400': form.errors.nova_senha }" />
            <p v-if="form.errors.nova_senha" class="text-xs text-red-600 mt-0.5">{{ form.errors.nova_senha }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nova senha</label>
            <input v-model="form.nova_senha_confirmation" type="password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              :class="{ 'border-red-400': form.errors.nova_senha_confirmation }" />
            <p v-if="form.errors.nova_senha_confirmation" class="text-xs text-red-600 mt-0.5">{{ form.errors.nova_senha_confirmation }}</p>
          </div>
          <div class="pt-1">
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg hover:bg-green-600 disabled:opacity-60 transition-colors">
              {{ form.processing ? 'Salvando...' : 'Alterar Senha' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Info admin -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3">Conta</h2>
        <p class="text-sm text-gray-600">E-mail: <span class="text-gray-800 font-medium">{{ $page.props.auth?.admin?.email ?? '—' }}</span></p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const pendentes = { empresas: 0, prestadores: 0, propostas: 0 }

const form = useForm({ senha_atual: '', nova_senha: '', nova_senha_confirmation: '' })

function salvar() {
  form.put(route('admin.perfil.update'), {
    onSuccess: () => form.reset(),
  })
}
</script>
