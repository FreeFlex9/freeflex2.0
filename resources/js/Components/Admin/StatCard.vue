<template>
  <Link :href="href" class="block bg-white rounded-xl shadow p-6 hover:shadow-md transition-shadow">
    <p class="text-sm font-medium text-gray-500">{{ label }}</p>
    <p class="text-3xl font-bold mt-1" :class="colorClass">{{ value }}</p>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  label: String,
  value: [Number, String],
  color: { type: String, default: 'green' },
  href: { type: String, default: '#' },
})

const colorClass = computed(() => ({
  'text-yellow-500': props.color === 'yellow',
  'text-blue-500':   props.color === 'blue',
  'text-orange-500': props.color === 'orange',
  'text-green-600':  props.color === 'green',
  'text-gray-600':   props.color === 'gray',
}))
</script>
