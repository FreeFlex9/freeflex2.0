<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="route('home')" class="flex flex-col items-center">
        <div class="w-20 h-20 flex items-center justify-center rounded-full shadow-lg shadow-emerald-100">
            <img src="/images/logoFreeFlex.png" alt="Logo" class="w-20 h-20 rounded-full" />
        </div>
        <span class="text-xl font-bold text-gray-900">Free<span class="text-emerald-500">Flex</span></span>
    </Link>
</template>
