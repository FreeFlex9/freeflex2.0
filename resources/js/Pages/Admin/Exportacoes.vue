<template>
  <AdminLayout title="Exportações">
    <div class="mt-2 max-w-2xl space-y-4">

      <p class="text-sm text-gray-500">
        Exporte os dados em formato CSV — compatível com Excel, Google Planilhas e LibreOffice.
      </p>

      <!-- Prestadores -->
      <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div>
            <p class="font-semibold text-gray-800">Prestadores</p>
            <p class="text-xs text-gray-400 mt-0.5">
              Nome, CPF, e-mail, telefone, status, CNH, serviços, cidade/estado, datas de cadastro e aprovação
            </p>
          </div>
        </div>
        <a :href="route('admin.exportar.prestadores')"
          class="shrink-0 flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Baixar CSV
        </a>
      </div>

      <!-- Empresas -->
      <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div>
            <p class="font-semibold text-gray-800">Empresas</p>
            <p class="text-xs text-gray-400 mt-0.5">
              Razão social, CNPJ, e-mail, telefone, status, cidade/estado, datas de cadastro e aprovação
            </p>
          </div>
        </div>
        <a :href="route('admin.exportar.empresas')"
          class="shrink-0 flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Baixar CSV
        </a>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>
