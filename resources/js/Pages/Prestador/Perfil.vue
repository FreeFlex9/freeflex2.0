<template>
  <PrestadorLayout title="Meu Perfil">

    <!-- CNH: recém solicitada (flash) -->
    <div v-if="$page.props.flash?.cnh_notice"
      class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <strong>CNH solicitada!</strong>
        Faça o upload dos documentos da sua CNH na seção <strong>Documentos</strong> abaixo.
        O administrador irá revisar e aprovar antes de liberar os serviços que exigem habilitação.
      </div>
    </div>

    <!-- CNH: aguardando aprovação -->
    <div v-else-if="provider.cnh_status === 'pending'"
      class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <strong>CNH aguardando aprovação.</strong>
        Seus documentos de CNH estão sendo analisados pelo administrador.
        Assim que aprovados, você poderá oferecer serviços que exigem habilitação.
      </div>
    </div>

    <!-- CNH: rejeitada -->
    <div v-else-if="provider.cnh_status === 'rejected'"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 flex items-start gap-3">
      <svg class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <strong>CNH rejeitada.</strong>
        <span v-if="provider.cnh_rejection_reason"> Motivo: {{ provider.cnh_rejection_reason }}.</span>
        Você pode reenviar os documentos corretos na seção <strong>Documentos</strong> abaixo ou desabilitar a CNH.
      </div>
    </div>

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

      <!-- ── Coluna esquerda ── -->
      <div class="space-y-4">

        <!-- Foto + dados pessoais (somente leitura) -->
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

          <label class="cursor-pointer w-full">
            <input type="file" class="hidden" accept="image/*" @change="upload('profile_photo', $event)" />
            <span class="block text-center text-xs font-medium text-orange-500 border border-orange-300 rounded-lg py-1.5 hover:bg-orange-50 transition">
              {{ provider.profile_photo_path ? 'Trocar foto' : 'Adicionar foto' }}
            </span>
          </label>
          <p v-if="uploadErrors.profile_photo" class="text-xs text-red-500 text-center">{{ uploadErrors.profile_photo }}</p>

          <!-- Dados pessoais somente leitura -->
          <div class="w-full space-y-2 pt-1">
            <div>
              <p class="text-xs font-medium text-gray-400">Nome</p>
              <p class="text-sm text-gray-800 font-medium">{{ provider.name }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400">E-mail</p>
              <p class="text-sm text-gray-700">{{ provider.email }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-400">CPF</p>
              <p class="text-sm text-gray-700 font-mono tracking-wide">{{ formatCpf(provider.cpf) }}</p>
            </div>
            <div v-if="provider.birth_date">
              <p class="text-xs font-medium text-gray-400">Data de nascimento</p>
              <p class="text-sm text-gray-700">{{ formatDate(provider.birth_date) }}</p>
            </div>
          </div>
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

      <!-- ── Coluna direita ── -->
      <div class="lg:col-span-2 space-y-4">

        <!-- Informações editáveis -->
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
                  <template v-if="provider.cnh_status === 'pending'">Aguardando aprovação do administrador</template>
                  <template v-else-if="provider.cnh_status === 'rejected'">Rejeitada — reenvie os documentos ou desabilite</template>
                  <template v-else-if="infoForm.has_license">Habilitado para serviços que exigem CNH</template>
                  <template v-else>Não habilitado para serviços com CNH</template>
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

            <!-- Toggle PCD -->
            <div class="flex items-start justify-between gap-4 pt-1">
              <div>
                <p class="text-sm font-medium text-gray-700">Sou Pessoa com Deficiência (PCD)</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Essa informação pode ser vista pelas empresas ao analisar sua candidatura.
                </p>
              </div>
              <button type="button" @click="infoForm.is_pcd = !infoForm.is_pcd"
                class="shrink-0 w-12 h-6 rounded-full relative transition-colors duration-200 focus:outline-none mt-0.5"
                :class="infoForm.is_pcd ? 'bg-orange-500' : 'bg-gray-200'">
                <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
                  :class="infoForm.is_pcd ? 'translate-x-6' : 'translate-x-0'" />
              </button>
            </div>

            <div v-if="infoForm.is_pcd">
              <label class="text-xs font-medium text-gray-500">Tipo de deficiência (opcional)</label>
              <input v-model="infoForm.pcd_type" type="text"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                placeholder="Ex: física, visual, auditiva..." />
            </div>

            <button type="submit" :disabled="infoForm.processing"
              class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium py-2 rounded-lg transition disabled:opacity-60">
              Salvar informações
            </button>
          </form>
        </div>

        <!-- Endereço -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 text-sm">Endereço</h3>
            <button v-if="!editingAddress" type="button" @click="editingAddress = true"
              class="text-xs text-orange-500 font-medium hover:text-orange-600 transition">
              Editar
            </button>
          </div>

          <!-- Modo leitura -->
          <div v-if="!editingAddress" class="space-y-2">
            <div v-if="hasAddress">
              <p class="text-xs font-medium text-gray-400">CEP</p>
              <p class="text-sm text-gray-700">{{ provider.zip_code }}</p>
            </div>
            <div v-if="provider.street">
              <p class="text-xs font-medium text-gray-400">Logradouro</p>
              <p class="text-sm text-gray-700">
                {{ provider.street }}{{ provider.number ? ', ' + provider.number : '' }}
                <span v-if="provider.complement" class="text-gray-500"> · {{ provider.complement }}</span>
              </p>
            </div>
            <div v-if="provider.neighborhood">
              <p class="text-xs font-medium text-gray-400">Bairro</p>
              <p class="text-sm text-gray-700">{{ provider.neighborhood }}</p>
            </div>
            <div v-if="provider.city || provider.state">
              <p class="text-xs font-medium text-gray-400">Cidade / Estado</p>
              <p class="text-sm text-gray-700">{{ [provider.city, provider.state].filter(Boolean).join(' – ') }}</p>
            </div>
            <p v-if="!hasAddress" class="text-sm text-gray-400 italic">Nenhum endereço cadastrado.</p>
          </div>

          <!-- Modo edição -->
          <form v-else @submit.prevent="saveAddress" class="grid gap-3">
            <div class="grid grid-cols-3  gap-3">
              <div>
                <label class="text-xs font-medium text-gray-500">CEP</label>
                <input v-model="addressForm.zip_code" type="text"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                placeholder="00000-000" @blur="fetchAddress" maxlength="9" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Estado</label>
                <select v-model="addressForm.state"
                  class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                  <option value="">Selecione</option>
                  <option v-for="uf in ufs" :key="uf" :value="uf">{{ uf }}</option>
                </select>
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Número</label>
                <input v-model="addressForm.number" type="text"
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2  gap-3">
              <div>
                <label class="text-xs font-medium text-gray-500">Logradouro</label>
                <input v-model="addressForm.street" type="text"
                  class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Complemento</label>
                <input v-model="addressForm.complement" type="text"
                  class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                  placeholder="Apto, sala, etc." />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Bairro</label>
                <input v-model="addressForm.neighborhood" type="text"
                  class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Cidade</label>
                <input v-model="addressForm.city" type="text"
                  class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
              </div>
            </div>
            <div class="flex gap-2 pt-1">
              <button type="submit" :disabled="addressForm.processing"
                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium py-2 rounded-lg transition disabled:opacity-60">
                Salvar endereço
              </button>
              <button type="button" @click="cancelAddress"
                class="px-4 py-2 text-sm text-gray-500 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancelar
              </button>
            </div>
          </form>
        </div>

        <!-- Documentos -->
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
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-700">
                    {{ doc.label }}
                    <span v-if="doc.obrigatorio" class="text-red-400 text-xs">*</span>
                  </p>
                  <p class="text-xs" :class="doc.path ? 'text-green-600' : 'text-gray-400'">
                    {{ doc.path ? 'Enviado ✓' : 'Não enviado' }}
                  </p>
                  <p v-if="uploadErrors[doc.tipo]" class="text-xs text-red-500 mt-0.5">{{ uploadErrors[doc.tipo] }}</p>
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
import { useDarkMode } from '@/composables/useDarkMode.js'

const { isDark, toggle } = useDarkMode()

const props = defineProps({ provider: Object })

// ── Formatação ──────────────────────────────────────────────────────────────

function formatCpf(cpf) {
  if (!cpf) return '—'
  const d = cpf.replace(/\D/g, '')
  if (d.length !== 11) return cpf
  return `${d.slice(0,3)}.${d.slice(3,6)}.${d.slice(6,9)}-${d.slice(9)}`
}

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.slice(0, 10).split('-')
  return `${day}/${m}/${y}`
}

// ── Formulário: informações ──────────────────────────────────────────────────

const infoForm = useForm({
  phone:       props.provider.phone       ?? '',
  bio:         props.provider.bio         ?? '',
  // Toggle ON se CNH aprovada, pendente ou rejeitada (usuário expressou intenção)
  has_license: props.provider.has_license || ['pending', 'rejected'].includes(props.provider.cnh_status),
  is_pcd:      props.provider.is_pcd      ?? false,
  pcd_type:    props.provider.pcd_type    ?? '',
})

const removendoCnh = computed(() =>
  props.provider.has_license && !infoForm.has_license
)

function saveInfo() {
  infoForm.put(route('prestador.perfil.update'))
}

// ── Formulário: endereço ─────────────────────────────────────────────────────

const addressForm = useForm({
  zip_code:     props.provider.zip_code     ?? '',
  street:       props.provider.street       ?? '',
  number:       props.provider.number       ?? '',
  complement:   props.provider.complement   ?? '',
  neighborhood: props.provider.neighborhood ?? '',
  city:         props.provider.city         ?? '',
  state:        props.provider.state        ?? '',
})

const editingAddress = ref(false)

const hasAddress = computed(() =>
  !!(props.provider.zip_code || props.provider.street || props.provider.city)
)

function saveAddress() {
  addressForm.put(route('prestador.perfil.endereco'), {
    onSuccess: () => { editingAddress.value = false },
  })
}

function cancelAddress() {
  addressForm.reset()
  editingAddress.value = false
}

async function fetchAddress() {
  const cep = addressForm.zip_code.replace(/\D/g, '')
  if (cep.length !== 8) return
  try {
    const res = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    if (res.ok) {
      const data = await res.json()
      addressForm.street       = data.street       || addressForm.street
      addressForm.neighborhood = data.neighborhood || addressForm.neighborhood
      addressForm.city         = data.city         || addressForm.city
      addressForm.state        = data.state        || addressForm.state
    }
  } catch {}
}

const ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']

// ── Formulário: senha ────────────────────────────────────────────────────────

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

// ── Upload de documentos ─────────────────────────────────────────────────────

const uploading = ref(null)
const uploadErrors = ref({})

const MAX_FILE_SIZE = 5 * 1024 * 1024 // 5 MB, mesmo limite do backend
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf']

function upload(tipo, event) {
  const file = event.target?.files?.[0]
  const input = event.target
  if (!file) return

  delete uploadErrors.value[tipo]

  const extensao = file.name.split('.').pop()?.toLowerCase()
  if (!ALLOWED_EXTENSIONS.includes(extensao)) {
    uploadErrors.value = { ...uploadErrors.value, [tipo]: 'Formato inválido. Envie um arquivo JPG, PNG, WebP ou PDF.' }
    if (input) input.value = ''
    return
  }
  if (file.size > MAX_FILE_SIZE) {
    uploadErrors.value = { ...uploadErrors.value, [tipo]: 'Arquivo muito grande. Máximo 5 MB.' }
    if (input) input.value = ''
    return
  }

  uploading.value = tipo

  const form = new FormData()
  form.append('tipo', tipo)
  form.append('arquivo', file)

  router.post(route('prestador.perfil.documento'), form, {
    forceFormData: true,
    onSuccess: () => { delete uploadErrors.value[tipo] },
    onError: (errors) => {
      uploadErrors.value = {
        ...uploadErrors.value,
        [tipo]: errors.arquivo || errors.tipo || 'Falha ao enviar o arquivo. Tente novamente.',
      }
    },
    onFinish: () => {
      uploading.value = null
      if (input) input.value = ''
    },
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
    { tipo: 'rg_front',      label: 'RG (frente)',                obrigatorio: true, path: p.rg_front_path },
    { tipo: 'rg_back',       label: 'RG (verso)',                 obrigatorio: true, path: p.rg_back_path },
    { tipo: 'address_proof', label: 'Comprovante de Residência',  obrigatorio: true, path: p.address_proof_path },
  ]
  const showCnh = p.has_license || ['pending', 'rejected'].includes(p.cnh_status)
  if (showCnh) {
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
