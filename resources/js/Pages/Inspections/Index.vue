<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { Link } from '@inertiajs/vue3'
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
</script>

<template>
    <div>
        <AppHeader title="Inspections" />

        <main class="mx-auto max-w-6xl p-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-semibold">Recent Inspections</h2>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Link :href="route('inspections.pre')" class="rounded bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white shadow-sm">
                        New Pre Trip
                    </Link>
                    <Link :href="route('inspections.post')" class="rounded bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white shadow-sm">
                        New Post Trip
                    </Link>
                </div>
            </div>

            <div v-if="inspectionRows.length" class="space-y-3">
                <div v-for="inspection in inspectionRows" :key="inspection.id" class="rounded-lg border p-4">
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xl font-semibold">{{ inspection.vehicle?.name || 'Vehicle' }}</div>
                        <div class="text-sm text-gray-600">
                            {{ inspection.paired_post_trip ? 'Trip Complete' : 'Pre Trip Open' }}
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <section>
                            <div class="mb-2 text-lg font-semibold">Pre Trip</div>
                            <div>Submitted: {{ formatSubmittedAt(inspection.created_at) }}</div>
                            <div>Driver: {{ inspection.driver_name }}</div>
                            <div>Starting Mileage: {{ inspection.starting_mileage }}</div>
                            <div v-if="inspection.fuel_level">Fuel Level: {{ inspection.fuel_level }}</div>
                            <div v-if="inspection.damage_notes">Damage Notes: {{ inspection.damage_notes }}</div>
                        </section>

                        <section v-if="inspection.paired_post_trip" class="border-t pt-4 md:border-l md:border-t-0 md:pl-4 md:pt-0">
                            <div class="mb-2 text-lg font-semibold">Post Trip</div>
                            <div>Submitted: {{ formatSubmittedAt(inspection.paired_post_trip.created_at) }}</div>
                            <div>Driver: {{ inspection.paired_post_trip.driver_name }}</div>
                            <div>Starting Mileage: {{ inspection.paired_post_trip.starting_mileage }}</div>
                            <div>Ending Mileage: {{ inspection.paired_post_trip.ending_mileage }}</div>
                            <div v-if="inspection.paired_post_trip.fuel_level">Fuel Level: {{ inspection.paired_post_trip.fuel_level }}</div>
                            <div v-if="inspection.paired_post_trip.damage_notes">Damage Notes: {{ inspection.paired_post_trip.damage_notes }}</div>
                        </section>

                        <section v-else class="border-t pt-4 text-gray-600 md:border-l md:border-t-0 md:pl-4 md:pt-0">
                            <div class="mb-2 text-lg font-semibold">Post Trip</div>
                            <div>Not completed</div>
                        </section>
                    </div>
                </div>
            </div>

            <p v-else>No inspections have been submitted.</p>
        </main>
    </div>
</template>
