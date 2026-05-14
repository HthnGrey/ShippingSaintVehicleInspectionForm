<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    drivers: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    selectedRange: {
        type: String,
        default: '30_days'
    }
})

const totalMiles = computed(() => {
    return props.drivers.reduce((sum, driver) => sum + Number(driver.total_miles || 0), 0)
})

const totalTrips = computed(() => {
    return props.drivers.reduce((sum, driver) => sum + Number(driver.trip_count || 0), 0)
})

function changeRange(range) {
    router.get(route('driver-mileage.index'), { range }, {
        preserveScroll: true,
        preserveState: true,
    })
}

function formatMiles(value) {
    return Number(value || 0).toLocaleString()
}

function formatDate(value) {
    if (!value) {
        return 'No trips'
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Driver Mileage" />

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Total Miles</div>
                    <div class="mt-3 text-4xl font-semibold text-white">{{ formatMiles(totalMiles) }}</div>
                </article>

                <article class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Completed Trips</div>
                    <div class="mt-3 text-4xl font-semibold text-white">{{ formatMiles(totalTrips) }}</div>
                </article>
            </section>

            <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Mileage By Driver</h2>
                        <p class="mt-1 text-sm text-slate-400">Calculated from completed post-trip inspections.</p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a :href="route('exports.driver-mileage', { range: selectedRange })" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-blue-400 hover:text-white">
                            Export CSV
                        </a>
                        <label class="sr-only" for="mileage-range">Mileage range</label>
                        <select
                            id="mileage-range"
                            :value="selectedRange"
                            class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-semibold text-slate-300 transition focus:border-blue-400 focus:ring-blue-400"
                            @change="changeRange($event.target.value)"
                        >
                            <option v-for="(label, range) in filters" :key="range" :value="range">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="drivers.length" class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="border-b border-slate-800 text-xs uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="py-3 pr-4">Driver</th>
                                <th class="py-3 pr-4">Miles Driven</th>
                                <th class="py-3 pr-4">Trips</th>
                                <th class="py-3 pr-4">Last Trip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="driver in drivers" :key="driver.driver_name">
                                <td class="py-4 pr-4 font-semibold text-white">{{ driver.driver_name }}</td>
                                <td class="py-4 pr-4 text-slate-100">{{ formatMiles(driver.total_miles) }}</td>
                                <td class="py-4 pr-4 text-slate-300">{{ formatMiles(driver.trip_count) }}</td>
                                <td class="py-4 pr-4 text-slate-400">{{ formatDate(driver.last_trip_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">
                    No completed post-trip mileage was found for this time range.
                </p>
            </section>
        </main>
    </div>
</template>
