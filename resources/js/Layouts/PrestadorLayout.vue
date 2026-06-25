<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300"
      :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-5 border-b border-gray-100">
        <span class="text-xl font-bold text-orange-500">FreeFlex</span>
        <button class="lg:hidden text-gray-400 hover:text-gray-600" @click="open = false">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Provider info -->
      <div class="px-5 py-4 border-b border-gray-100">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1">Prestador</p>
        <p class="font-semibold text-gray-800 text-sm truncate">{{ $page.props.auth.provider?.name }}</p>
        <span :class="statusClass" class="inline-block text-xs px-2 py-0.5 rounded-full mt-1 font-medium">{{ statusLabel }}</span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
        <NavItem :href="route('prestador.dashboard')" icon="home">Dashboard</NavItem>
        <NavItem href="#" icon="search">Buscar Demandas</NavItem>
        <NavItem href="#" icon="file">Minhas Propostas</NavItem>
        <NavItem href="#" icon="calendar">Minha Agenda</NavItem>
        <NavItem href="#" icon="briefcase">Meus Serviços</NavItem>
        <div class="pt-3 pb-1 px-3">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Conta</p>
        </div>
        <NavItem href="#" icon="star">Avaliações</NavItem>
        <NavItem :href="route('prestador.perfil')" icon="user">Perfil</NavItem>
      </nav>

      <!-- Logout -->
      <div class="p-4 border-t border-gray-100">
        <Link :href="route('prestador.logout')" method="post" as="button"
          class="w-full flex items-center gap-2 text-sm text-gray-500 hover:text-red-500 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Sair
        </Link>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div v-if="open" class="fixed inset-0 bg-black bg-opacity-30 z-40 lg:hidden" @click="open = false"/>

    <!-- Main -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Top bar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6 gap-4 sticky top-0 z-30">
        <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="open = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <h1 class="text-gray-800 font-semibold text-base flex-1">{{ title }}</h1>
        <slot name="header-actions" />
      </header>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-6 pt-4">
        <div v-if="$page.props.flash?.success" class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm mb-2">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm mb-2">
          {{ $page.props.flash.error }}
        </div>
      </div>

      <!-- Aviso conta pendente -->
      <div v-if="$page.props.auth.provider?.status === 'pending'" class="mx-6 mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div>
          <strong>Cadastro em análise.</strong> Seus documentos estão sendo verificados. Você poderá enviar propostas após a aprovação.
        </div>
      </div>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Painel Prestador' } })

const open = ref(false)
const page = usePage()

const statusClass = computed(() => ({
  'bg-yellow-100 text-yellow-700': page.props.auth.provider?.status === 'pending',
  'bg-green-100 text-green-700':   page.props.auth.provider?.status === 'approved',
  'bg-red-100 text-red-700':       page.props.auth.provider?.status === 'rejected',
}))

const statusLabel = computed(() => ({
  pending:  'Em análise',
  approved: 'Aprovado',
  rejected: 'Rejeitado',
}[page.props.auth.provider?.status] ?? ''))

const NavItem = {
  props: { href: String, icon: String },
  setup(props, { slots }) {
    const page = usePage()
    const isActive = computed(() => props.href !== '#' && page.url.startsWith(new URL(props.href, location.href).pathname))
    return { isActive }
  },
  template: `
    <a :href="href" :class="['flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors', isActive ? 'bg-orange-50 text-orange-600 font-medium' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800']">
      <span class="w-4 h-4 shrink-0 opacity-70">
        <svg v-if="icon==='home'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <svg v-else-if="icon==='search'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <svg v-else-if="icon==='file'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <svg v-else-if="icon==='calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <svg v-else-if="icon==='briefcase'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <svg v-else-if="icon==='star'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      </span>
      <slot />
    </a>
  `
}
</script>
