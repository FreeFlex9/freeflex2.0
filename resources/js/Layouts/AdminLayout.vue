<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white flex flex-col transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-6 bg-gray-800">
        <div class="flex items-center gap-2">
          <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-8 w-auto rounded-full" />
          <span class="text-xs font-semibold text-green-400 uppercase tracking-wider">Admin</span>
        </div>
        <button class="lg:hidden text-gray-400 hover:text-white" @click="sidebarOpen = false">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <NavItem :href="route('admin.dashboard')" icon="home">Dashboard</NavItem>

        <div class="pt-4 pb-1 px-3">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Aprovações</p>
        </div>
        <NavItem :href="route('admin.empresas.index')" icon="building">
          Empresas
          <span v-if="pendentes.empresas" class="ml-auto text-xs bg-red-500 text-white rounded-full px-2 py-0.5">
            {{ pendentes.empresas }}
          </span>
        </NavItem>
        <NavItem :href="route('admin.prestadores.index')" icon="users">
          Prestadores
          <span v-if="pendentes.prestadores" class="ml-auto text-xs bg-red-500 text-white rounded-full px-2 py-0.5">
            {{ pendentes.prestadores }}
          </span>
        </NavItem>
        <NavItem :href="route('admin.demandas.index')" icon="clipboard-check">
          Contratações
          <span v-if="pendentes.propostas" class="ml-auto text-xs bg-red-500 text-white rounded-full px-2 py-0.5">
            {{ pendentes.propostas }}
          </span>
        </NavItem>

        <div class="pt-4 pb-1 px-3">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestão</p>
        </div>
        <NavItem :href="route('admin.demandas.index')" icon="list">Demandas</NavItem>
        <NavItem :href="route('admin.servicos.index')" icon="briefcase">Serviços</NavItem>

        <div class="pt-4 pb-1 px-3">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Configurações</p>
        </div>
        <NavItem :href="route('admin.config-email.index')" icon="mail">Config E-mail</NavItem>
        <NavItem :href="route('admin.perfil.edit')" icon="user">Perfil</NavItem>
      </nav>

      <!-- Logout -->
      <div class="p-4 border-t border-gray-700">
        <div class="text-xs text-gray-400 mb-2 truncate">{{ $page.props.auth.admin?.email }}</div>
        <Link
          :href="route('admin.logout')"
          method="post"
          as="button"
          class="w-full text-left text-sm text-gray-300 hover:text-white flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Sair
        </Link>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Main -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Top bar -->
      <header class="h-16 bg-white shadow flex items-center px-6 gap-4">
        <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="sidebarOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h1 class="text-gray-700 font-semibold text-lg flex-1">{{ title }}</h1>
      </header>

      <!-- Flash messages -->
      <div class="px-6 pt-4">
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-start gap-2">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ $page.props.flash.success }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-start gap-2">
          <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          {{ errorMessage }}
        </div>
      </div>

      <!-- Content -->
      <main class="flex-1 px-6 pb-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavItem from '@/Components/Admin/NavItem.vue'

defineProps({
  title: { type: String, default: 'Painel Admin' },
  pendentes: { type: Object, default: () => ({ empresas: 0, prestadores: 0, propostas: 0 }) },
})

const sidebarOpen = ref(false)
const page = usePage()

const errorMessage = computed(() => {
  const errors = page.props.errors
  if (!errors) return null
  const first = Object.values(errors)[0]
  return Array.isArray(first) ? first[0] : first
})
</script>
