<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { router } from '@inertiajs/vue3'

defineProps({
    inspections: {
        type: Array,
        default: () => []
    },
    drivers: {
        type: Array,
        default: () => []
    },
    selectedDriver: {
        type: String,
        default: ''
    },
    canViewAllDrivers: {
        type: Boolean,
        default: false
    }
})

function changeDriver(driver) {
    router.get(route('driver-history.index'), { driver: driver || undefined }, {
        preserveScroll: true,
        preserveState: true,
    })
}

function formatSubmittedAt(value) {
    if (!value) {
        return 'Not available'
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Driver History" />

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
            <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Inspection History</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ canViewAllDrivers ? 'Review inspections by driver.' : 'Your submitted inspections.' }}</p>
                    </div>

                    <select v-if="canViewAllDrivers" :value="selectedDriver || ''" class="rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400" @change="changeDriver($event.target.value)">
                        <option value="">All drivers</option>
                        <option v-for="driver in drivers" :key="driver" :value="driver">{{ driver }}</option>
                    </select>
                </div>

                <div v-if="inspections.length" class="space-y-3">
                    <article v-for="inspection in inspections" :key="inspection.id" class="rounded-xl border border-slate-800 bg-slate-950/70 p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ inspection.vehicle?.name || inspection.pre_trip?.vehicle?.name || 'Vehicle' }}</h3>
                                <p class="text-sm text-slate-400">{{ inspection.inspection_type }} by {{ inspection.driver_name }}</p>
                            </div>
                            <div class="text-sm text-blue-300">{{ formatSubmittedAt(inspection.created_at) }}</div>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
                            <div>Starting Mileage: {{ inspection.starting_mileage }}</div>
                            <div v-if="inspection.ending_mileage">Ending Mileage: {{ inspection.ending_mileage }}</div>
                            <div v-if="inspection.fuel_level">Fuel Level: {{ inspection.fuel_level }}</div>
                            <div>Damage Found: {{ inspection.damage_found ? 'Yes' : 'No' }}</div>
                        </div>
                        <p v-if="inspection.damage_notes" class="mt-3 rounded-xl border border-slate-800 bg-slate-900 p-3 text-sm text-slate-300">{{ inspection.damage_notes }}</p>
                        <a v-if="inspection.damage_photo_url" :href="inspection.damage_photo_url" target="_blank" class="mt-3 inline-block text-sm font-semibold text-blue-300 hover:text-blue-200">View damage photo</a>
                    </article>
                </div>

                <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">
                    No inspections were found.
                </p>
            </section>
        </main>
    </div>
</template>
