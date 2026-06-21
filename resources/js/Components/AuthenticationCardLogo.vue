<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="route('home')" class="flex flex-col items-center gap-2">
        <div class="w-12 h-12 rounded-xl bg-emerald-500 dark:bg-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-200">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>

            
        </div>
        <span class="text-xl font-bold text-gray-900">Free<span class="text-emerald-500">Flex</span></span>
    </Link>
</template>
