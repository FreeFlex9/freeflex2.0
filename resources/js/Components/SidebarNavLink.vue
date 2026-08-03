<template>
  <Link
    :href="href"
    :title="collapsed ? label : undefined"
    class="flex items-center rounded-lg transition-colors"
    :class="[
      active ? activeClasses : inactiveClasses,
      collapsed ? 'justify-center p-2' : 'gap-2.5 px-3 py-2',
    ]"
  >
    <span class="relative shrink-0">
      <slot />
      <span
        v-if="badge > 0 && collapsed"
        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] leading-none rounded-full min-w-[14px] h-3.5 flex items-center justify-center px-0.5"
      >
        {{ badge > 9 ? '9+' : badge }}
      </span>
    </span>
    <span v-if="!collapsed" class="text-sm truncate flex-1">{{ label }}</span>
    <span
      v-if="!collapsed && badge > 0"
      class="text-xs bg-red-500 text-white rounded-full px-1.5 py-0.5 leading-none shrink-0"
    >
      {{ badge > 9 ? '9+' : badge }}
    </span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: String,
  active: Boolean,
  collapsed: Boolean,
  label: String,
  activeColor: { type: String, default: 'orange' },
  badge: { type: Number, default: 0 },
})

const activeClasses = computed(() => props.activeColor === 'teal'
  ? 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-500/10 dark:text-teal-400'
  : 'bg-orange-50 text-orange-600 font-medium dark:bg-orange-500/10 dark:text-orange-400')

const inactiveClasses = 'text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'
</script>
