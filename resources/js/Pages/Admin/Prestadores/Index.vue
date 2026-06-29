<template>
  <AdminLayout title="Aprovações de Prestadores" :pendentes="pendentes">
    <div class="mt-2">
      <div v-if="prestadores.length === 0" class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4">
        Nenhum prestador pendente de aprovação no momento.
      </div>

      <div v-else class="space-y-6">
        <div v-for="pres in prestadores" :key="pres.id" class="bg-white rounded-xl shadow p-6">

          <!-- Cabeçalho: dados + ações -->
          <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-800 text-lg">{{ pres.name }}</h3>
              <div class="mt-1 space-y-0.5 text-sm text-gray-500">
                <p>CPF: <span class="text-gray-700 font-medium">{{ pres.cpf }}</span></p>
                <p>E-mail: {{ pres.email }}</p>
                <p v-if="pres.phone">Tel: {{ pres.phone }}</p>
                <p v-if="pres.bio" class="italic text-gray-400">{{ pres.bio }}</p>
                <p>Cadastro: {{ formatDate(pres.created_at) }}</p>
              </div>
            </div>

            <div class="flex flex-col gap-2 items-end">
              <div class="flex gap-2">
                <button @click="aprovar(pres)" :disabled="!docsOk(pres)"
                  class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  :title="docsOk(pres) ? 'Aprovar' : 'Documentos obrigatórios pendentes'">
                  Aprovar
                </button>
                <button @click="abrirRejeicao(pres)"
                  class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors">
                  Rejeitar
                </button>
              </div>
              <p v-if="!docsOk(pres)" class="text-xs text-red-500">Docs obrigatórios pendentes</p>
            </div>
          </div>

          <!-- Documentos -->
          <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Documentos</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">

              <!-- RG ou CNH -->
              <template v-if="pres.has_license">
                <DocCard
                  :path="pres.license_front_path"
                  :label="pres.is_digital_license ? 'CNH (arquivo)' : 'CNH (frente)'"
                  obrigatorio />
                <DocCard v-if="!pres.is_digital_license"
                  :path="pres.license_back_path"
                  label="CNH (verso)" />
              </template>
              <template v-else>
                <DocCard :path="pres.rg_front_path" label="RG (frente)" obrigatorio />
                <DocCard :path="pres.rg_back_path"  label="RG (verso)"  obrigatorio />
              </template>

              <!-- CCMEI -->
              <DocCard v-if="pres.mei_cnpj"
                :path="pres.ccmei_path"
                label="CCMEI (MEI)"
                obrigatorio />

              <!-- Foto de perfil (opcional, apenas informativo) -->
              <DocCard v-if="pres.profile_photo_path || true"
                :path="pres.profile_photo_path"
                label="Foto de perfil"
                :obrigatorio="false" />

            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Modal confirmar aprovação -->
    <ConfirmModal
      :show="!!confirmAprovar"
      title="Aprovar prestador"
      :message="`Aprovar o prestador &quot;${confirmAprovar?.name}&quot;? O acesso completo será liberado.`"
      confirm-text="Aprovar"
      variant="success"
      @confirm="confirmarAprovar"
      @cancel="confirmAprovar = null" />

    <!-- Modal rejeição -->
    <div v-if="modalPrestador" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Rejeitar: {{ modalPrestador.name }}</h3>
        <textarea v-model="motivo" rows="4"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
          placeholder="Descreva o motivo..." />
        <p v-if="erroMotivo" class="text-sm text-red-600 mt-1">{{ erroMotivo }}</p>
        <div class="flex gap-2 mt-4">
          <button @click="fecharModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm">Cancelar</button>
          <button @click="rejeitar" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">Confirmar Rejeição</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, h, resolveComponent } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

// Componente de card de documento (SFC inline com render function — sem template string)
const DocCard = {
  props: {
    path:       { type: String, default: null },
    label:      { type: String, required: true },
    obrigatorio:{ type: Boolean, default: false },
  },
  setup(props) {
    const isPdf = (p) => p && p.toLowerCase().endsWith('.pdf')

    return () => {
      const { path, label, obrigatorio } = props
      const hasdoc = !!path

      // Container
      return h('div', {
        class: [
          'rounded-lg border overflow-hidden flex flex-col',
          hasdoc ? 'border-green-200' : obrigatorio ? 'border-red-200' : 'border-gray-200',
        ]
      }, [
        // Preview area
        h('div', {
          class: [
            'h-32 flex items-center justify-center relative',
            hasdoc ? 'bg-green-50' : obrigatorio ? 'bg-red-50' : 'bg-gray-50',
          ]
        }, hasdoc
          ? (isPdf(path)
              ? [
                  h('div', { class: 'flex flex-col items-center gap-1' }, [
                    h('svg', { class: 'w-10 h-10 text-red-400', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' },
                      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
                    ),
                    h('span', { class: 'text-xs text-red-400 font-medium' }, 'PDF'),
                  ])
                ]
              : [
                  h('img', {
                    src: '/storage/' + path,
                    class: 'w-full h-full object-cover',
                    alt: label,
                  }),
                ]
            )
          : [
              h('div', { class: 'flex flex-col items-center gap-1' }, [
                h('svg', { class: 'w-8 h-8 text-gray-300', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' },
                  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
                ),
                h('span', { class: 'text-xs text-gray-400' }, 'Não enviado'),
              ])
            ]
        ),
        // Footer
        h('div', { class: 'px-2 py-1.5 flex items-center justify-between bg-white' }, [
          h('span', { class: 'text-xs text-gray-600 font-medium truncate' }, [
            label,
            obrigatorio ? h('span', { class: 'text-red-400 ml-0.5' }, '*') : null,
          ]),
          hasdoc
            ? h('a', {
                href: '/storage/' + path,
                target: '_blank',
                class: 'text-xs text-blue-500 hover:underline shrink-0 ml-1',
              }, 'Ver')
            : h('span', { class: 'text-xs text-gray-300' }, '—'),
        ]),
      ])
    }
  }
}

const props = defineProps({ prestadores: Array })
const pendentes = { empresas: 0, prestadores: props.prestadores.length, propostas: 0 }
const modalPrestador = ref(null)
const confirmAprovar = ref(null)
const motivo = ref('')
const erroMotivo = ref('')

function formatDate(d) { return d ? new Date(d).toLocaleDateString('pt-BR') : '-' }

function docsOk(pres) {
  if (pres.has_license) {
    if (pres.is_digital_license) return !!pres.license_front_path
    return !!(pres.license_front_path && pres.license_back_path)
  }
  if (!pres.rg_front_path || !pres.rg_back_path) return false
  if (pres.mei_cnpj && !pres.ccmei_path) return false
  return true
}

function aprovar(pres) {
  confirmAprovar.value = pres
}

function confirmarAprovar() {
  useForm({}).post(route('admin.prestadores.aprovar', confirmAprovar.value.id), {
    onFinish: () => { confirmAprovar.value = null },
  })
}

function abrirRejeicao(pres) { modalPrestador.value = pres; motivo.value = ''; erroMotivo.value = '' }
function fecharModal() { modalPrestador.value = null }

function rejeitar() {
  if (!motivo.value.trim()) { erroMotivo.value = 'O motivo é obrigatório.'; return }
  useForm({ motivo: motivo.value }).post(route('admin.prestadores.rejeitar', modalPrestador.value.id), {
    onSuccess: () => fecharModal(),
  })
}
</script>
