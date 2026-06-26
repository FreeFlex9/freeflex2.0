<template>
  <PrestadorLayout title="Dashboard">
    <div class="space-y-6">

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2 leading-tight">Propostas Enviadas</p>
          <p class="text-3xl font-bold text-orange-600">{{ stats.proposals_sent ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2 leading-tight">Aguardando Resposta</p>
          <p class="text-3xl font-bold text-amber-600">{{ stats.proposals_pending ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2 leading-tight">Agendamentos</p>
          <p class="text-3xl font-bold text-blue-600">{{ stats.scheduled ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2 leading-tight">Nota Média</p>
          <p class="text-3xl font-bold text-yellow-500">{{ stats.avg_rating ? Number(stats.avg_rating).toFixed(1) : '—' }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2 leading-tight">Avaliações</p>
          <p class="text-3xl font-bold text-gray-600">{{ stats.ratings_count ?? 0 }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Próximos Agendamentos -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Próximos Agendamentos</h2>
          </div>
          <div v-if="upcomingSchedules.length" class="divide-y divide-gray-50">
            <div v-for="s in upcomingSchedules" :key="s.id" class="px-5 py-3.5">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ s.demand?.company?.trade_name }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ s.demand?.service?.name }} · {{ formatDate(s.date) }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ s.start_time }} – {{ s.end_time }}</p>
                </div>
                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">Agendado</span>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-10 text-center text-sm text-gray-400">
            Nenhum agendamento futuro.
          </div>
        </div>

        <!-- Minhas Propostas Recentes -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Minhas Propostas</h2>
            <Link :href="route('prestador.propostas.index')" class="text-xs text-orange-500 hover:underline">Ver todas</Link>
          </div>
          <div v-if="recentProposals.length" class="divide-y divide-gray-50">
            <div v-for="p in recentProposals" :key="p.id"
              class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50">
              <div>
                <p class="text-sm font-medium text-gray-800">{{ p.demand?.title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ p.demand?.service?.name }}</p>
              </div>
              <span :class="proposalClass(p.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
                {{ proposalLabel(p.status) }}
              </span>
            </div>
          </div>
          <div v-else class="px-5 py-10 text-center text-sm text-gray-400">
            Você ainda não enviou propostas.
          </div>
        </div>

      </div>

      <!-- Demandas Disponíveis -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-800 text-sm">Demandas Disponíveis para Você</h2>
          <Link :href="route('prestador.demandas.index')" class="text-xs text-orange-500 hover:underline">Ver mais</Link>
        </div>
        <div v-if="availableDemands.length" class="divide-y divide-gray-50">
          <div v-for="d in availableDemands" :key="d.id"
            class="px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ d.title }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ d.service?.name }} · {{ d.company?.trade_name }} · {{ formatDate(d.date) }} · {{ d.start_time }}–{{ d.end_time }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">
                {{ d.neighborhood }}, {{ d.city }}/{{ d.state }}
                <span v-if="d.slots_needed > 1"> · {{ d.slots_needed }} vagas</span>
              </p>
            </div>
            <Link :href="route('prestador.demandas.index')"
              class="ml-4 shrink-0 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
              Ver demanda
            </Link>
          </div>
        </div>
        <div v-else class="px-5 py-10 text-center text-sm text-gray-400">
          Sem demandas abertas para seus serviços no momento.
        </div>
      </div>

    </div>
  </PrestadorLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue'

defineProps({
  provider: Object,
  stats: { type: Object, default: () => ({}) },
  upcomingSchedules: { type: Array, default: () => [] },
  recentProposals: { type: Array, default: () => [] },
  availableDemands: { type: Array, default: () => [] },
})

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.slice(0, 10).split('-')
  return `${day}/${m}/${y}`
}

const proposalMap = {
  pending:                { label: 'Enviada',          bg: 'bg-gray-100',  text: 'text-gray-600' },
  pending_admin_approval: { label: 'Em análise',       bg: 'bg-yellow-50', text: 'text-yellow-700' },
  pending_company_accept: { label: 'Aguard. empresa',  bg: 'bg-blue-50',   text: 'text-blue-700' },
  accepted:               { label: 'Aceita',           bg: 'bg-green-50',  text: 'text-green-700' },
  rejected:               { label: 'Recusada',         bg: 'bg-red-50',    text: 'text-red-600' },
  rejected_admin:         { label: 'Recusada (admin)', bg: 'bg-red-50',    text: 'text-red-600' },
  rejected_provider:      { label: 'Cancelada',        bg: 'bg-gray-100',  text: 'text-gray-500' },
}

function proposalClass(s) {
  const m = proposalMap[s]
  return m ? `${m.bg} ${m.text}` : 'bg-gray-100 text-gray-500'
}

function proposalLabel(s) {
  return proposalMap[s]?.label ?? s
}
</script>
