<template>
  <AdminLayout title="Dashboard" :pendentes="pendentes">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
      <StatCard
        label="Empresas Pendentes"
        :value="stats.empresas_pendentes"
        color="yellow"
        :href="route('admin.empresas.index')"
      />
      <StatCard
        label="Prestadores Pendentes"
        :value="stats.prestadores_pendentes"
        color="blue"
        :href="route('admin.prestadores.index')"
      />
      <StatCard
        label="Contratações Pendentes"
        :value="stats.propostas_pendentes"
        color="orange"
        :href="route('admin.demandas.propostas')"
      />
      <StatCard
        label="Demandas Abertas"
        :value="stats.demandas_abertas"
        color="green"
        :href="route('admin.demandas.index')"
      />
      <StatCard
        label="Total de Empresas"
        :value="stats.total_empresas"
        color="gray"
        :href="route('admin.empresas.index')"
      />
      <StatCard
        label="Total de Prestadores"
        :value="stats.total_prestadores"
        color="gray"
        :href="route('admin.prestadores.index')"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/Admin/StatCard.vue'

const props = defineProps({
  stats: Object,
})

const pendentes = {
  empresas: props.stats.empresas_pendentes,
  prestadores: props.stats.prestadores_pendentes,
  propostas: props.stats.propostas_pendentes,
}
</script>
