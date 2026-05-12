<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import DriveTimeWidget from '@/Components/DriveTimeWidget.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    vehicles: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions || {})

const inUseVehicles = computed(() => props.vehicles.filter(vehicle => String(vehicle.status).toLowerCase() === 'in use'))
const availableVehicles = computed(() => props.vehicles.filter(vehicle => String(vehicle.status).toLowerCase() === 'available'))
const maintenanceVehicles = computed(() => {
    return props.vehicles.filter(vehicle => {
        const status = String(vehicle.status).toLowerCase()

        return status === 'maintenance required' || status === 'needs maintenance'
    })
})

const totalVehicles = computed(() => props.vehicles.length)
const activePercent = computed(() => percent(inUseVehicles.value.length))
const availablePercent = computed(() => percent(availableVehicles.value.length))
const maintenancePercent = computed(() => percent(maintenanceVehicles.value.length))

const statCards = computed(() => [
    {
        label: 'Available',
        value: availableVehicles.value.length,
        icon: '🚐',
        color: 'green',
        description: 'Ready for dispatch'
    },
    {
        label: 'In Use',
        value: inUseVehicles.value.length,
        icon: '🛣',
        color: 'blue',
        description: 'Currently assigned'
    },
    {
        label: 'Maintenance',
        value: maintenanceVehicles.value.length,
        icon: '🛠',
        color: 'amber',
        description: 'Needs attention'
    },
    {
        label: 'Fleet Total',
        value: totalVehicles.value,
        icon: '📊',
        color: 'slate',
        description: 'Tracked vehicles'
    },
])

function percent(value) {
    if (!totalVehicles.value) {
        return 0
    }

    return Math.round((value / totalVehicles.value) * 100)
}

function formatMileage(value) {
    return Number(value || 0).toLocaleString()
}

function vehicleSubtitle(vehicle) {
    return vehicle.plate_number ? `Plate ${vehicle.plate_number}` : 'Fleet vehicle'
}

function vehicleAccent(status) {
    const normalizedStatus = String(status).toLowerCase()

    if (normalizedStatus === 'available') {
        return 'border-green-400/40 text-green-300 bg-green-500/10'
    }

    if (normalizedStatus === 'in use') {
        return 'border-blue-400/40 text-blue-300 bg-blue-500/10'
    }

    return 'border-amber-400/40 text-amber-300 bg-amber-500/10'
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Vehicle Dashboard" />

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ stat.label }}</p>
                            <div class="mt-3 text-4xl font-semibold text-white">{{ stat.value }}</div>
                        </div>
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-xl border text-xl shadow-lg"
                            :class="{
                                'border-green-400/30 bg-green-500/15 shadow-green-500/10': stat.color === 'green',
                                'border-blue-400/30 bg-blue-500/15 shadow-blue-500/10': stat.color === 'blue',
                                'border-amber-400/30 bg-amber-500/15 shadow-amber-500/10': stat.color === 'amber',
                                'border-slate-600 bg-slate-800 shadow-slate-950/30': stat.color === 'slate',
                            }"
                        >
                            {{ stat.icon }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-400">{{ stat.description }}</p>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Vehicle Activity</h2>
                            <p class="text-sm text-slate-400">Fleet allocation by current status</p>
                        </div>
                        <span class="rounded-full border border-blue-400/30 bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-200">
                            {{ totalVehicles }} total
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-slate-300">In use</span>
                                <span class="text-blue-300">{{ activePercent }}%</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-blue-500" :style="{ width: `${activePercent}%` }"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-slate-300">Available</span>
                                <span class="text-green-300">{{ availablePercent }}%</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-green-500" :style="{ width: `${availablePercent}%` }"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="text-slate-300">Maintenance</span>
                                <span class="text-amber-300">{{ maintenancePercent }}%</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-amber-400" :style="{ width: `${maintenancePercent}%` }"></div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                    <h2 class="text-xl font-semibold text-white">Maintenance Load</h2>
                    <p class="text-sm text-slate-400">Vehicles flagged for service</p>

                    <div class="mt-6 flex items-end gap-4">
                        <div class="flex h-44 flex-1 items-end rounded-2xl bg-slate-950/80 p-4">
                            <div class="w-full rounded-xl bg-gradient-to-t from-amber-500 to-green-400" :style="{ height: `${Math.max(maintenancePercent, 8)}%` }"></div>
                        </div>
                        <div>
                            <div class="text-5xl font-semibold text-white">{{ maintenanceVehicles.length }}</div>
                            <div class="mt-1 text-sm text-slate-400">open work orders</div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-xl font-semibold text-white">Available Vehicles</h2>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-green-300">{{ availableVehicles.length }} ready</span>
                            <Link v-if="permissions.createInspections" :href="route('inspections.pre')" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500">
                                Start Inspection
                            </Link>
                        </div>
                    </div>

                    <div v-if="availableVehicles.length" class="grid gap-4">
                        <div v-for="vehicle in availableVehicles" :key="vehicle.id" class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">🚐</span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-white">{{ vehicle.name }}</h3>
                                            <p class="text-sm text-slate-400">{{ vehicleSubtitle(vehicle) }}</p>
                                        </div>
                                    </div>
                                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <dt class="text-slate-500">Mileage</dt>
                                            <dd class="font-semibold text-slate-100">{{ formatMileage(vehicle.current_mileage) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-slate-500">Status</dt>
                                            <dd><span class="rounded-full border px-2 py-1 text-xs font-semibold" :class="vehicleAccent(vehicle.status)">{{ vehicle.status }}</span></dd>
                                        </div>
                                    </dl>
                                </div>
                                <div class="flex gap-2">
                                    <Link v-if="permissions.viewVehicles" :href="route('vehicles.index')" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white">
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">No vehicles are available.</p>
                </article>

                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">In Use Vehicles</h2>
                        <span class="text-sm text-blue-300">{{ inUseVehicles.length }} active</span>
                    </div>

                    <div v-if="inUseVehicles.length" class="grid gap-4">
                        <div v-for="vehicle in inUseVehicles" :key="vehicle.id" class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-white">🛣 {{ vehicle.name }}</div>
                                    <p class="text-sm text-slate-400">{{ vehicleSubtitle(vehicle) }}</p>
                                    <div class="mt-3 text-sm text-slate-300">Mileage: {{ formatMileage(vehicle.current_mileage) }}</div>
                                </div>
                                <div class="flex flex-col gap-3 sm:items-end">
                                    <span class="rounded-full border px-2 py-1 text-xs font-semibold" :class="vehicleAccent(vehicle.status)">{{ vehicle.status }}</span>
                                    <div class="flex gap-2">
                                        <Link v-if="permissions.viewVehicles" :href="route('vehicles.index')" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white">
                                            View
                                        </Link>
                                        <Link v-if="permissions.createInspections" :href="route('inspections.post')" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500">
                                            Start Post Inspection
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">No vehicles are currently in use.</p>
                </article>
            </section>

            <section v-if="maintenanceVehicles.length" class="rounded-2xl border border-amber-400/30 bg-amber-500/10 p-6 shadow-xl shadow-amber-950/20">
                <h2 class="text-xl font-semibold text-white">Maintenance Required</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="vehicle in maintenanceVehicles" :key="vehicle.id" class="rounded-xl border border-amber-400/20 bg-slate-950/70 p-4">
                        <div class="text-lg font-semibold text-white">🛠 {{ vehicle.name }}</div>
                        <div class="mt-2 text-sm text-slate-300">Mileage: {{ formatMileage(vehicle.current_mileage) }}</div>
                        <div v-if="vehicle.required_maintenance_mileage" class="text-sm text-amber-200">
                            Service due: {{ formatMileage(vehicle.required_maintenance_mileage) }}
                        </div>
                    </div>
                </div>
            </section>

            <DriveTimeWidget />
        </main>
    </div>
</template>
