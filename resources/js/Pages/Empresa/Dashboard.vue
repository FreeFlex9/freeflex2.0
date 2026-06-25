<template>
  <EmpresaLayout title="Dashboard">
    <div class="space-y-6">

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2">Demandas Abertas</p>
          <p class="text-3xl font-bold text-teal-700">{{ stats.open_demands ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2">Propostas Pendentes</p>
          <p class="text-3xl font-bold text-amber-600">{{ stats.pending_proposals ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2">Agendadas</p>
          <p class="text-3xl font-bold text-blue-600">{{ stats.scheduled ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
          <p class="text-xs text-gray-500 font-medium mb-2">Concluídas</p>
          <p class="text-3xl font-bold text-green-600">{{ stats.completed ?? 0 }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Demandas Recentes -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Demandas Recentes</h2>
            <a href="#" class="text-xs text-teal-600 hover:underline">Ver todas</a>
          </div>
          <div v-if="recentDemands.length" class="divide-y divide-gray-50">
            <div v-for="d in recentDemands" :key="d.id"
              class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div>
                <p class="text-sm font-medium text-gray-800">{{ d.title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ d.service?.name }} · {{ formatDate(d.date) }}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">{{ d.proposals_count }} proposta{{ d.proposals_count !== 1 ? 's' : '' }}</span>
                <span :class="statusClass(d.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">{{ statusLabel(d.status) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-10 text-center text-sm text-gray-400">
            Nenhuma demanda ainda.
            <br/>
            <a href="#" class="text-teal-600 font-medium hover:underline mt-1 inline-block">Criar primeira demanda</a>
          </div>
        </div>

        <!-- Próximos Agendamentos -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Próximos Agendamentos</h2>
          </div>
          <div v-if="upcomingSchedules.length" class="divide-y divide-gray-50">
            <div v-for="s in upcomingSchedules" :key="s.id" class="px-5 py-3.5">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ s.provider?.name }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ s.demand?.service?.name }} · {{ formatDate(s.date) }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ s.start_time }} – {{ s.end_time }}</p>
                </div>
                <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">
                  Agendado
                </span>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-10 text-center text-sm text-gray-400">
            Sem agendamentos futuros.
          </div>
        </div>

      </div>
    </div>
  </EmpresaLayout>
</template>

<script setup>
import EmpresaLayout from '@/Layouts/EmpresaLayout.vue'

defineProps({
  company: Object,
  stats: { type: Object, default: () => ({}) },
  recentDemands: { type: Array, default: () => [] },
  upcomingSchedules: { type: Array, default: () => [] },
})

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

const statusMap = {
  open:                   { label: 'Aberta',           bg: 'bg-teal-50',   text: 'text-teal-700' },
  partially_scheduled:    { label: 'Parcial',          bg: 'bg-blue-50',   text: 'text-blue-700' },
  scheduled:              { label: 'Agendada',         bg: 'bg-blue-50',   text: 'text-blue-700' },
  in_progress:            { label: 'Em andamento',     bg: 'bg-amber-50',  text: 'text-amber-700' },
  completed:              { label: 'Concluída',        bg: 'bg-green-50',  text: 'text-green-700' },
  cancelled:              { label: 'Cancelada',        bg: 'bg-gray-100',  text: 'text-gray-500' },
  pending_admin_approval: { label: 'Aguard. admin',    bg: 'bg-yellow-50', text: 'text-yellow-700' },
}

function statusClass(s) {
  const m = statusMap[s]
  return m ? `${m.bg} ${m.text}` : 'bg-gray-100 text-gray-500'
}

function statusLabel(s) {
  return statusMap[s]?.label ?? s
}
</script>
