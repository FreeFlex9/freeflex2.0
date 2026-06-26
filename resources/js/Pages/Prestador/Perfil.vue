<template>
  <PrestadorLayout title="Meu Perfil">

    <!-- Aviso de documentos pendentes -->
    <div v-if="missingRequired.length && provider.status === 'pending'"
      class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div>
        <strong>Complete seu cadastro para ser aprovado.</strong>
        Envie: {{ missingRequired.map(d => d.label).join(', ') }}.
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Coluna esquerda: foto + info + senha -->
      <div class="space-y-4">

        <!-- Foto de perfil -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col items-center gap-3">
          <div class="relative">
            <img v-if="provider.profile_photo_path"
              :src="`/storage/${provider.profile_photo_path}`"
              class="w-24 h-24 rounded-full object-cover border-2 border-orange-200" />
            <div v-else class="w-24 h-24 rounded-full bg-orange-100 flex items-center justify-center text-orange-400">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>
          <p class="font-semibold text-gray-800 text-sm text-center">{{ provider.name }}</p>
          <p class="text-xs text-gray-500">{{ provider.email }}</p>
          <label class="cursor-pointer w-full">
            <input type="file" class="hidden" accept="image/*" @change="upload('profile_photo', $event)" />
            <span class="block text-center text-xs font-medium text-orange-500 border border-orange-300 rounded-lg py-1.5 hover:bg-orange-50 transition">
              {{ provider.profile_photo_path ? 'Trocar foto' : 'Adicionar foto' }}
            </span>
          </label>
        </div>

        <!-- Informações básicas -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 text-sm mb-4">Informações</h3>
          <form @submit.prevent="saveInfo" class="space-y-3">
            <div>
              <label class="text-xs font-medium text-gray-500">Telefone</label>
              <input v-model="infoForm.phone" type="text"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                placeholder="(00) 00000-0000" />
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Bio</label>
              <textarea v-model="infoForm.bio" rows="3"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"
                placeholder="Sobre você..." />
            </div>

            <!-- Toggle CNH -->
            <div class="flex items-start justify-between gap-4 pt-1">
              <div>
                <p class="text-sm font-medium text-gray-700">Possuo Carteira de Motorista (CNH)</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  {{ infoForm.has_license ? 'Habilitado para serviços que exigem CNH' : 'Não habilitado para serviços com CNH' }}
                </p>
              </div>
              <button type="button" @click="infoForm.has_license = !infoForm.has_license"
                class="shrink-0 w-12 h-6 rounded-full relative transition-colors duration-200 focus:outline-none mt-0.5"
                :class="infoForm.has_license ? 'bg-orange-500' : 'bg-gray-200'">
                <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
                  :class="infoForm.has_license ? 'translate-x-6' : 'translate-x-0'" />
              </button>
            </div>

            <div v-if="removendoCnh" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-2">
              Ao salvar sem CNH, os serviços que exigem habilitação serão removidos dos seus serviços.
            </div>

            <button type="submit" :disabled="infoForm.processing"
              class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium py-2 rounded-lg transition disabled:opacity-60">
              Salvar
            </button>
          </form>
        </div>

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

      </div>

      <!-- Coluna direita: documentos -->
      <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 text-sm mb-1">Documentos</h3>
          <p class="text-xs text-gray-400 mb-5">JPG, PNG ou PDF · máx. 5 MB por arquivo</p>

          <div class="space-y-3">
            <div v-for="doc in documentos" :key="doc.tipo"
              class="flex items-center justify-between p-3 rounded-lg border"
              :class="doc.path ? 'border-green-200 bg-green-50' : doc.obrigatorio ? 'border-red-100 bg-red-50' : 'border-gray-200 bg-gray-50'">

              <div class="flex items-center gap-3 min-w-0">
                <div class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                  :class="doc.path ? 'bg-green-100' : 'bg-gray-200'">
                  <svg v-if="doc.path" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700">
                    {{ doc.label }}
                    <span v-if="doc.obrigatorio" class="text-red-400 text-xs">*</span>
                  </p>
                  <p class="text-xs" :class="doc.path ? 'text-green-600' : 'text-gray-400'">
                    {{ doc.path ? 'Enviado ✓' : 'Não enviado' }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0 ml-3">
                <a v-if="doc.path" :href="'/storage/' + doc.path" target="_blank"
                  class="text-xs text-blue-500 hover:underline">Ver</a>
                <label class="cursor-pointer">
                  <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                    @change="upload(doc.tipo, $event)"
                    :disabled="uploading === doc.tipo" />
                  <span class="text-xs font-medium text-orange-500 border border-orange-300 rounded px-2 py-1 hover:bg-orange-50 transition"
                    :class="uploading === doc.tipo ? 'opacity-50 cursor-not-allowed' : ''">
                    {{ uploading === doc.tipo ? 'Enviando...' : doc.path ? 'Trocar' : 'Enviar' }}
                  </span>
                </label>
                <button v-if="doc.path" type="button" @click="remove(doc.tipo)"
                  class="text-xs text-red-400 hover:text-red-600">✕</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </PrestadorLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

const props = defineProps({ provider: Object })

const infoForm = useForm({
  phone:       props.provider.phone       ?? '',
  bio:         props.provider.bio         ?? '',
  has_license: props.provider.has_license ?? false,
})

const removendoCnh = computed(() =>
  props.provider.has_license && !infoForm.has_license
)

function saveInfo() {
  infoForm.put(route('prestador.perfil.update'))
}

const passForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

function savePassword() {
  passForm.put(route('prestador.perfil.senha'), {
    onSuccess: () => passForm.reset(),
  })
}

const uploading = ref(null)

function upload(tipo, event) {
  const file = event.target?.files?.[0]
  if (!file) return
  uploading.value = tipo

  const form = new FormData()
  form.append('tipo', tipo)
  form.append('arquivo', file)

  router.post(route('prestador.perfil.documento'), form, {
    forceFormData: true,
    onFinish: () => { uploading.value = null },
  })
}

function remove(tipo) {
  router.delete(route('prestador.perfil.documento.remove'), {
    data: { tipo },
  })
}

const documentos = computed(() => {
  const p = props.provider
  const list = [
    { tipo: 'rg_front',  label: 'RG (frente)', obrigatorio: true,  path: p.rg_front_path },
    { tipo: 'rg_back',   label: 'RG (verso)',  obrigatorio: true,  path: p.rg_back_path },
  ]
  if (p.has_license) {
    list.push({ tipo: 'license_front', label: 'CNH (frente)', obrigatorio: true,  path: p.license_front_path })
    list.push({ tipo: 'license_back',  label: 'CNH (verso)',  obrigatorio: false, path: p.license_back_path })
  }
  if (p.mei_cnpj) {
    list.push({ tipo: 'ccmei', label: 'CCMEI (MEI)', obrigatorio: true, path: p.ccmei_path })
  }
  return list
})

const missingRequired = computed(() =>
  documentos.value.filter(d => d.obrigatorio && !d.path)
)
</script>
