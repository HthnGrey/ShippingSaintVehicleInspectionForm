<script setup>
import AppHeader from '@/Components/AppHeader.vue'

defineProps({
    title: {
        type: String,
        default: 'Vehicle Dashboard',
    },
})
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader :title="title" />

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
            <slot />
        </main>
    </div>
</template>
