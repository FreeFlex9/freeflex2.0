<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex">

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300"
      :class="[
        collapsed ? 'w-16' : 'w-64',
        open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center border-b border-gray-100 dark:border-gray-800 shrink-0"
        :class="collapsed ? 'justify-center px-2' : 'justify-between px-5'">
        <div class="flex justify-center items-center gap-2">
          <img src="/images/logoFreeFlex.png" alt="FreeFlex" class="h-8 w-auto rounded-full" />
          <span v-if="!collapsed" class="text-xl font-bold text-teal-600">FreeFlex</span>
        </div>

        <button v-if="!collapsed" class="lg:hidden text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300" @click="open = false">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Company info -->
      <div v-if="!collapsed" class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
        <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium mb-1">Empresa</p>
        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm truncate">{{ $page.props.auth.company?.trade_name }}</p>
        <span :class="statusClass" class="inline-block text-xs px-2 py-0.5 rounded-full mt-1 font-medium">{{ statusLabel }}</span>
      </div>

      <!-- Toggle desktop -->
      <button class="hidden lg:flex w-full items-center py-2 border-t border-gray-100 dark:border-gray-800 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        :class="collapsed ? 'justify-center px-2' : 'justify-end px-4'" @click="toggleCollapse"
        :title="collapsed ? 'Expandir menu' : 'Recolher menu'">
        <svg class="w-4 h-4 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 space-y-0.5" :class="collapsed ? 'px-2' : 'px-3'">

        <NavLink :href="route('empresa.dashboard')" :active="isActive('/empresa/dashboard')"
          :collapsed="collapsed" label="Dashboard" active-color="teal">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
        </NavLink>

        <NavLink :href="route('empresa.demandas.create')" :active="isActive('/empresa/demandas/nova')"
          :collapsed="collapsed" label="Nova Demanda" active-color="teal">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </NavLink>

        <NavLink :href="route('empresa.demandas.index')"
          :active="isActive('/empresa/demandas') && !isActive('/empresa/demandas/nova')"
          :collapsed="collapsed" label="Minhas Demandas" active-color="teal">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </NavLink>

        <!-- Divider -->
        <div :class="collapsed ? 'my-2 mx-1 border-t border-gray-100 dark:border-gray-800' : 'pt-3 pb-1 px-3'">
          <p v-if="!collapsed" class="text-xs font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-wider">Conta</p>
        </div>

        <NavLink :href="route('empresa.avaliacoes.index')" :active="isActive('/empresa/avaliacoes')"
          :collapsed="collapsed" label="Avaliações" active-color="teal">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </NavLink>

        <NavLink :href="route('empresa.perfil')" :active="isActive('/empresa/perfil')"
          :collapsed="collapsed" label="Perfil" active-color="teal">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </NavLink>

      </nav>

      <!-- Footer: logout -->
      <div class="border-t border-gray-100 dark:border-gray-800 shrink-0">
        <div :class="collapsed ? 'flex justify-center p-2' : 'px-4 py-3'">
          <Link :href="route('empresa.logout')" method="post" as="button"
            :title="collapsed ? 'Sair' : undefined"
            class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
            :class="collapsed ? 'p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20' : 'w-full'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span v-if="!collapsed">Sair</span>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div v-if="open" class="fixed inset-0 bg-black bg-opacity-30 z-40 lg:hidden" @click="open = false"/>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
      :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'">

      <!-- Top bar -->
      <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center px-6 gap-4 sticky top-0 z-30">
        <button class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" @click="open = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h1 class="text-gray-800 dark:text-gray-100 font-semibold text-base flex-1">{{ title }}</h1>
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
      <div v-if="$page.props.auth.company?.status === 'pending'" class="mx-6 mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
          <strong>Conta em análise.</strong> Seu cadastro está sendo verificado pelo administrador. Você poderá criar demandas após a aprovação.
        </div>
      </div>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Painel Empresa' } })

const open = ref(false)
const collapsed = ref(localStorage.getItem('sidebar-empresa') === 'true')
const page = usePage()

function toggleCollapse() {
  collapsed.value = !collapsed.value
  localStorage.setItem('sidebar-empresa', collapsed.value)
}

function isActive(path) {
  return page.url.startsWith(path)
}

const statusClass = computed(() => ({
  'bg-yellow-100 text-yellow-700': page.props.auth.company?.status === 'pending',
  'bg-green-100 text-green-700':   page.props.auth.company?.status === 'approved',
  'bg-red-100 text-red-700':       page.props.auth.company?.status === 'rejected',
}))

const statusLabel = computed(() => ({
  pending:  'Em análise',
  approved: 'Aprovada',
  rejected: 'Rejeitada',
}[page.props.auth.company?.status] ?? ''))

const NavLink = {
  props: {
    href: String,
    active: Boolean,
    collapsed: Boolean,
    label: String,
    activeColor: { type: String, default: 'teal' },
  },
  setup(props, { slots }) {
    return () => {
      const activeClasses = props.activeColor === 'teal'
        ? 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-500/10 dark:text-teal-400'
        : 'bg-orange-50 text-orange-600 font-medium dark:bg-orange-500/10 dark:text-orange-400'
      const inactiveClasses = 'text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'
      return h(Link, {
        href: props.href,
        title: props.collapsed ? props.label : undefined,
        class: [
          'flex items-center rounded-lg transition-colors',
          props.active ? activeClasses : inactiveClasses,
          props.collapsed ? 'justify-center p-2' : 'gap-2.5 px-3 py-2',
        ],
      }, () => [
        slots.default?.(),
        !props.collapsed ? h('span', { class: 'text-sm truncate' }, props.label) : null,
      ])
    }
  },
}
</script>
