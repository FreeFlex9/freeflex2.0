<template>
  <div class="flex flex-col gap-1">
    <label class="text-sm font-medium text-gray-700">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <label class="cursor-pointer">
      <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp,.pdf" @change="onChange" />
      <span
        class="flex items-center justify-between gap-2 w-full px-4 py-2.5 border rounded-lg text-sm transition"
        :class="error ? 'border-red-400' : modelValue ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:bg-gray-50'"
      >
        <span class="truncate" :class="modelValue ? 'text-green-700' : 'text-gray-400'">
          {{ modelValue ? modelValue.name : 'Selecionar arquivo...' }}
        </span>
        <svg v-if="modelValue" class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </span>
    </label>
    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    <p v-else-if="modelValue" class="text-xs text-green-600">Arquivo selecionado ✓</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  required: { type: Boolean, default: false },
  modelValue: { type: [File, null], default: null },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

function onChange(e) {
  emit('update:modelValue', e.target.files[0] ?? null)
}
</script>
