<script setup>
defineProps({
    wide: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <slot name="logo" />
        </div>

        <div
            :class="[
                'w-full mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg',
                wide ? 'sm:max-w-2xl' : 'sm:max-w-md',
            ]"
        >
            <slot />
        </div>
    </div>
</template>
