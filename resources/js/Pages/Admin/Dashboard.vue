<template>
  <AdminLayout title="Dashboard" :pendentes="pendentes">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
      <StatCard label="Empresas Pendentes"     :value="stats.companies_pending" color="yellow" :href="route('admin.empresas.index')" />
      <StatCard label="Prestadores Pendentes"  :value="stats.providers_pending" color="blue"   :href="route('admin.prestadores.index')" />
      <StatCard label="Contratações Pendentes" :value="stats.proposals_pending" color="orange" :href="route('admin.demandas.index')" />
      <StatCard label="Demandas Abertas"       :value="stats.demands_open"      color="green"  :href="route('admin.demandas.index')" />
      <StatCard label="Total de Empresas"      :value="stats.companies_total"   color="gray"   :href="route('admin.empresas.index')" />
      <StatCard label="Total de Prestadores"   :value="stats.providers_total"   color="gray"   :href="route('admin.prestadores.index')" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/Admin/StatCard.vue'

const props = defineProps({ stats: Object })

const pendentes = {
  empresas:    props.stats.companies_pending,
  prestadores: props.stats.providers_pending,
  propostas:   props.stats.proposals_pending,
}
</script>
