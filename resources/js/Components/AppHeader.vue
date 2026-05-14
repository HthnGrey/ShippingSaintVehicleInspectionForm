<script setup>
import Dropdown from '@/Components/Dropdown.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'

defineProps({
    title: {
        type: String,
        required: true
    }
})

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions || {})
const isMobileMenuOpen = ref(false)

const navigationItems = computed(() => [
    { label: 'Dashboard', icon: 'D', routeName: 'dashboard', active: 'dashboard', visible: permissions.value.viewDashboard },
    { label: 'Vehicles', icon: 'V', routeName: 'vehicles.index', active: 'vehicles.*', visible: permissions.value.viewVehicles },
    { label: 'Work Orders', icon: 'W', routeName: 'work-orders.index', active: 'work-orders.*', visible: permissions.value.manageWorkOrders },
    { label: 'Driver Mileage', icon: 'M', routeName: 'driver-mileage.index', active: 'driver-mileage.*', visible: permissions.value.viewDriverMileage },
    { label: 'Driver History', icon: 'H', routeName: 'driver-history.index', active: 'driver-history.*', visible: permissions.value.viewDriverHistory },
    { label: 'Inspections', icon: 'I', routeName: 'inspections.index', active: 'inspections.*', visible: permissions.value.viewInspections },
    { label: 'Reports', icon: 'R', routeName: 'reports.index', active: 'reports.*', visible: permissions.value.viewReports },
    { label: 'Audit Logs', icon: 'A', routeName: 'audit-logs.index', active: 'audit-logs.*', visible: permissions.value.viewAuditLogs },
    { label: 'Users', icon: 'U', routeName: 'users.index', active: 'users.*', visible: permissions.value.manageUsers },
].filter(item => item.visible))

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

watch(() => page.url, () => {
    isMobileMenuOpen.value = false
})
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-slate-800/80 bg-slate-950 px-5 py-6 text-slate-100 shadow-2xl shadow-black/30 lg:flex lg:flex-col">
        <Link :href="route('dashboard')" class="mb-8 flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/15 text-sm font-bold shadow-lg shadow-blue-500/10">
                SS
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
                <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-700 text-xs font-bold">{{ item.icon }}</span>
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
        </div>
    </aside>

    <div
        v-show="isMobileMenuOpen"
        class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden"
        @click="isMobileMenuOpen = false"
        aria-hidden="true"
    ></div>

    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-[min(20rem,calc(100vw-2rem))] flex-col border-r border-slate-800 bg-slate-950 px-5 py-5 text-slate-100 shadow-2xl shadow-black/40 transition-transform duration-200 lg:hidden"
        :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Mobile navigation"
    >
        <div class="mb-6 flex items-center justify-between gap-4">
            <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/15 text-sm font-bold shadow-lg shadow-blue-500/10">
                    SS
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-xs font-semibold uppercase tracking-wider text-blue-300">Shipping Saint</span>
                    <span class="block truncate text-base font-semibold text-white">Vehicle Control</span>
                </span>
            </Link>

            <button
                type="button"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-slate-200 transition hover:border-blue-400 hover:text-white"
                @click="isMobileMenuOpen = false"
                aria-label="Close menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="space-y-2 overflow-y-auto pb-4">
            <Link
                v-for="item in navigationItems"
                :key="item.label"
                :href="route(item.routeName)"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 hover:text-white"
                :class="route().current(item.active) ? 'border border-blue-400/30 bg-blue-500/15 text-blue-100 shadow-lg shadow-blue-500/10' : 'text-slate-400'"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-700 text-xs font-bold">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
            </Link>
        </nav>

        <div class="mt-auto space-y-4 border-t border-slate-800 pt-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-white">Light</span>
                    <button
                        type="button"
                        class="relative h-7 w-14 rounded-full border border-slate-700 bg-slate-800 p-1 transition"
                        :class="isDarkMode ? 'border-blue-400/40 bg-blue-500/20' : 'border-slate-300 bg-slate-200'"
                        @click="isDarkMode = !isDarkMode"
                        :aria-pressed="isDarkMode"
                        aria-label="Toggle dark mode"
                    >
                        <span
                            class="block h-5 w-5 rounded-full bg-white shadow-lg transition"
                            :class="isDarkMode ? 'translate-x-7' : 'translate-x-0'"
                        ></span>
                    </button>
                    <span class="text-sm font-semibold text-white">Dark</span>
                </div>
            </div>

            <div>
                <div class="truncate text-sm font-semibold text-white">{{ $page.props.auth?.user?.name || 'Fleet User' }}</div>
                <div class="truncate text-xs text-slate-400">{{ $page.props.auth?.user?.email }}</div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <Link :href="route('profile.edit')" class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-blue-400 hover:text-white">
                    Account
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-blue-400 hover:text-white">
                    Log Out
                </Link>
            </div>
        </div>
    </aside>

    <header class="sticky top-0 z-20 border-b border-slate-800/80 bg-slate-950/95 px-4 py-4 text-slate-100 shadow-xl shadow-black/20 backdrop-blur lg:ml-72 lg:px-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-3">
                <button
                    type="button"
                    class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-slate-200 transition hover:border-blue-400 hover:text-white lg:hidden"
                    @click="isMobileMenuOpen = true"
                    aria-label="Open menu"
                    :aria-expanded="isMobileMenuOpen"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <div class="min-w-0">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-blue-300 sm:text-xs sm:tracking-[0.25em]">Operations Dashboard</p>
                    <h1 class="mt-1 truncate text-2xl font-semibold text-white sm:text-3xl">{{ title }}</h1>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <Dropdown align="right" width="48" contentClasses="border border-slate-700 bg-slate-900 py-1">
                    <template #trigger>
                        <button type="button" class="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 transition hover:border-blue-400">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500 text-sm font-semibold text-white">
                                {{ $page.props.auth?.user?.name?.charAt(0) || 'U' }}
                            </span>
                            <span class="hidden text-sm font-medium text-slate-200 sm:block">
                                {{ $page.props.auth?.user?.name || 'Fleet User' }}
                            </span>
                            <span class="hidden rounded-full border border-slate-700 px-2 py-1 text-xs font-semibold uppercase text-slate-400 sm:block">
                                {{ $page.props.auth?.user?.role || 'driver' }}
                            </span>
                            <span class="text-xs text-slate-500">v</span>
                        </button>
                    </template>

                    <template #content>
                        <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            Account Settings
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-left text-sm text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            Log Out
                        </Link>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
