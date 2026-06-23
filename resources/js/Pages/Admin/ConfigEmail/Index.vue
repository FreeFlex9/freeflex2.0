<template>
  <AdminLayout title="Configuração de E-mail (SMTP)" :pendentes="pendentes">
    <div class="max-w-lg">
      <div class="bg-white rounded-xl shadow p-6">
        <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 text-sm">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="salvar" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Host SMTP</label>
              <input v-model="form.smtp_host" type="text" placeholder="smtp.gmail.com"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="form.errors.smtp_host" class="text-xs text-red-600 mt-0.5">{{ form.errors.smtp_host }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Porta</label>
              <input v-model="form.smtp_port" type="number" placeholder="587"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="form.errors.smtp_port" class="text-xs text-red-600 mt-0.5">{{ form.errors.smtp_port }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Criptografia</label>
              <select v-model="form.smtp_secure" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
              </select>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Usuário / E-mail</label>
              <input v-model="form.smtp_username" type="email"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="form.errors.smtp_username" class="text-xs text-red-600 mt-0.5">{{ form.errors.smtp_username }}</p>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
              <input v-model="form.smtp_password" type="password" placeholder="Deixe em branco para não alterar"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome do remetente</label>
              <input v-model="form.email_from_name" type="text" placeholder="FreeFlex"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">E-mail remetente</label>
              <input v-model="form.email_from" type="email"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
              <p v-if="form.errors.email_from" class="text-xs text-red-600 mt-0.5">{{ form.errors.email_from }}</p>
            </div>
          </div>

          <div class="pt-2">
            <button type="submit" :disabled="form.processing"
              class="px-6 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg hover:bg-green-600 disabled:opacity-60 transition-colors">
              {{ form.processing ? 'Salvando...' : 'Salvar Configurações' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ config: Object })
const pendentes = { empresas: 0, prestadores: 0, propostas: 0 }

const form = useForm({
  smtp_host:       props.config?.smtp_host       ?? '',
  smtp_port:       props.config?.smtp_port       ?? 587,
  smtp_secure:     props.config?.smtp_secure     ?? 'tls',
  smtp_username:   props.config?.smtp_username   ?? '',
  smtp_password:   '',
  email_from_name: props.config?.email_from_name ?? '',
  email_from:      props.config?.email_from      ?? '',
})

function salvar() {
  form.put(route('admin.config-email.update'), {
    preserveScroll: true,
  })
}
</script>
