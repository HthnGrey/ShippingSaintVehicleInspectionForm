<script setup>
import { Link } from '@inertiajs/vue3'
import { onMounted, ref, watch } from 'vue'

defineProps({
    title: {
        type: String,
        required: true
    }
})

const navigationItems = [
    { label: 'Dashboard', icon: '🚚', routeName: 'dashboard', active: 'dashboard' },
    { label: 'Vehicles', icon: '🚐', routeName: 'vehicles.index', active: 'vehicles.*' },
    { label: 'Work Orders', icon: '🛠', routeName: 'work-orders.index', active: 'work-orders.*' },
    { label: 'Inspections', icon: '📋', routeName: 'inspections.index', active: 'inspections.*' },
    { label: 'Reports', icon: '📊', routeName: 'reports.index', active: 'reports.*' },
]

const isDarkMode = ref(true)

function applyTheme(isDark) {
    document.documentElement.dataset.theme = isDark ? 'dark' : 'light'
}

onMounted(() => {
    const savedTheme = localStorage.getItem('shipping-saint-theme')
    isDarkMode.value = savedTheme ? savedTheme === 'dark' : true
    applyTheme(isDarkMode.value)
})

watch(isDarkMode, value => {
    localStorage.setItem('shipping-saint-theme', value ? 'dark' : 'light')
    applyTheme(value)
})
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-slate-800/80 bg-slate-950 px-5 py-6 text-slate-100 shadow-2xl shadow-black/30 lg:flex lg:flex-col">
        <Link :href="route('dashboard')" class="mb-8 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/15 text-xl shadow-lg shadow-blue-500/10">
                🚚
            </span>
            <span>
                <span class="block text-sm font-semibold uppercase tracking-wider text-blue-300">Shipping Saint</span>
                <span class="block text-lg font-semibold text-white">Vehicle Control</span>
            </span>
        </Link>

        <nav class="space-y-2">
            <Link
                v-for="item in navigationItems"
                :key="item.label"
                :href="route(item.routeName)"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 hover:text-white"
                :class="route().current(item.active) ? 'border border-blue-400/30 bg-blue-500/15 text-blue-100 shadow-lg shadow-blue-500/10' : 'text-slate-400'"
            >
                <span class="text-lg">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
            </Link>

        </nav>

        <div class="mt-auto space-y-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-white">Light</span>
                    <button
                        type="button"
                        class="relative h-7 w-14 rounded-full border border-slate-700 bg-slate-800 p-1 transition"
                        :class="isDarkMode ? 'border-blue-400/40 bg-blue-500/20' : 'border-slate-300 bg-slate-200'"
                        @click="isDarkMode = !isDarkMode"
                        :aria-pressed="isDarkMode"
                    >
                        <span
                            class="block h-5 w-5 rounded-full bg-white shadow-lg transition"
                            :class="isDarkMode ? 'translate-x-7' : 'translate-x-0'"
                        ></span>
                    </button>
                    <span class="text-sm font-semibold text-white">Dark</span>
                </div>
                <div class="text-xs text-slate-400">{{ isDarkMode ? 'Dark mode active' : 'Light mode active' }}</div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4">
            <div class="text-sm font-semibold text-white">Fleet Health</div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full w-4/5 rounded-full bg-gradient-to-r from-green-400 to-blue-500"></div>
            </div>
            <div class="mt-2 text-xs text-slate-400">Live readiness score</div>
            </div>
        </div>
    </aside>

    <header class="sticky top-0 z-20 border-b border-slate-800/80 bg-slate-950/95 px-4 py-4 text-slate-100 shadow-xl shadow-black/20 backdrop-blur lg:ml-72 lg:px-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-300">Operations Dashboard</p>
                <h1 class="mt-1 text-2xl font-semibold text-white sm:text-3xl">{{ title }}</h1>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative block">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">⌕</span>
                    <input
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 py-2.5 pl-9 pr-4 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400 sm:w-72"
                        placeholder="Search fleet"
                        type="search"
                    />
                </label>

                <div class="flex items-center gap-3">
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-slate-300 transition hover:border-blue-400 hover:text-white">
                        🔔
                    </button>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500 text-sm font-semibold text-white">
                            {{ $page.props.auth?.user?.name?.charAt(0) || 'U' }}
                        </span>
                        <span class="hidden text-sm font-medium text-slate-200 sm:block">
                            {{ $page.props.auth?.user?.name || 'Fleet User' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <nav class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:hidden">
            <Link
                v-for="item in navigationItems"
                :key="item.label"
                :href="route(item.routeName)"
                class="rounded-xl border px-3 py-2 text-center text-xs font-semibold transition"
                :class="route().current(item.active) ? 'border-blue-400/40 bg-blue-500/15 text-blue-100' : 'border-slate-800 bg-slate-900 text-slate-400'"
            >
                <span class="mr-1">{{ item.icon }}</span>{{ item.label }}
            </Link>
        </nav>
    </header>
</template>
