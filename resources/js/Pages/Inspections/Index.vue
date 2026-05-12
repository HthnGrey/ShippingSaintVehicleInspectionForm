<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    inspections: {
        type: Array,
        default: () => []
    }
})

const inspectionRows = computed(() => {
    return props.inspections.filter(inspection => {
        return inspection.inspection_type === 'Pre Trip' || !inspection.pre_trip_inspection_id
    })
})

function formatSubmittedAt(value) {
    if (!value) {
        return 'Not available'
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value))
}

function deleteInspection(inspection) {
    if (!window.confirm(`Remove this ${inspection.inspection_type} inspection?`)) {
        return
    }

    router.delete(route('inspections.destroy', inspection.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Inspections" />

        <main class="mx-auto max-w-6xl px-4 py-6 lg:ml-72 lg:px-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-semibold text-white">Recent Inspections</h2>

                <div class="grid gap-3 sm:grid-cols-3">
                    <a :href="route('exports.inspections')" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-center text-sm font-semibold text-slate-100 transition hover:border-blue-400">
                        Export CSV
                    </a>
                    <Link :href="route('inspections.pre')" class="rounded-xl bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500">
                        New Pre Trip
                    </Link>
                    <Link :href="route('inspections.post')" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-center text-sm font-semibold text-slate-100 transition hover:border-blue-400">
                        New Post Trip
                    </Link>
                </div>
            </div>

            <div v-if="inspectionRows.length" class="space-y-3">
                <div v-for="inspection in inspectionRows" :key="inspection.id" class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xl font-semibold text-white">{{ inspection.vehicle?.name || 'Vehicle' }}</div>
                        <div class="text-sm text-blue-300">
                            {{ inspection.paired_post_trip ? 'Trip Complete' : 'Pre Trip Open' }}
                        </div>
                    </div>

                    <div class="grid gap-4 text-slate-300 md:grid-cols-2">
                        <section>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="text-lg font-semibold text-white">Pre Trip</div>
                                <div class="flex gap-2">
                                    <Link :href="route('inspections.edit', inspection.id)" class="rounded-lg border border-slate-700 px-3 py-1 text-xs font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white">
                                        Edit
                                    </Link>
                                    <button type="button" class="rounded-lg border border-red-400/40 px-3 py-1 text-xs font-semibold text-red-200 transition hover:border-red-300 hover:text-white" @click="deleteInspection(inspection)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <div>Submitted: {{ formatSubmittedAt(inspection.created_at) }}</div>
                            <div>Driver: {{ inspection.driver_name }}</div>
                            <div>Starting Mileage: {{ inspection.starting_mileage }}</div>
                            <div v-if="inspection.fuel_level">Fuel Level: {{ inspection.fuel_level }}</div>
                            <div v-if="inspection.damage_notes">Damage Notes: {{ inspection.damage_notes }}</div>
                        </section>

                        <section v-if="inspection.paired_post_trip" class="border-t border-slate-800 pt-4 md:border-l md:border-t-0 md:pl-4 md:pt-0">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="text-lg font-semibold text-white">Post Trip</div>
                                <div class="flex gap-2">
                                    <Link :href="route('inspections.edit', inspection.paired_post_trip.id)" class="rounded-lg border border-slate-700 px-3 py-1 text-xs font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white">
                                        Edit
                                    </Link>
                                    <button type="button" class="rounded-lg border border-red-400/40 px-3 py-1 text-xs font-semibold text-red-200 transition hover:border-red-300 hover:text-white" @click="deleteInspection(inspection.paired_post_trip)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <div>Submitted: {{ formatSubmittedAt(inspection.paired_post_trip.created_at) }}</div>
                            <div>Driver: {{ inspection.paired_post_trip.driver_name }}</div>
                            <div>Starting Mileage: {{ inspection.paired_post_trip.starting_mileage }}</div>
                            <div>Ending Mileage: {{ inspection.paired_post_trip.ending_mileage }}</div>
                            <div v-if="inspection.paired_post_trip.fuel_level">Fuel Level: {{ inspection.paired_post_trip.fuel_level }}</div>
                            <div v-if="inspection.paired_post_trip.damage_notes">Damage Notes: {{ inspection.paired_post_trip.damage_notes }}</div>
                            <a v-if="inspection.paired_post_trip.damage_photo_url" :href="inspection.paired_post_trip.damage_photo_url" target="_blank" class="mt-2 inline-block text-sm font-semibold text-blue-300 hover:text-blue-200">View damage photo</a>
                        </section>

                        <section v-else class="border-t border-slate-800 pt-4 text-slate-500 md:border-l md:border-t-0 md:pl-4 md:pt-0">
                            <div class="mb-2 text-lg font-semibold text-slate-300">Post Trip</div>
                            <div>Not completed</div>
                        </section>
                    </div>
                </div>
            </div>

            <p v-else class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 text-slate-400 shadow-xl shadow-black/20">No inspections have been submitted.</p>
        </main>
    </div>
</template>
