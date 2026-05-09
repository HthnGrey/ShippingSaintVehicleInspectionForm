<script setup>
import { computed } from 'vue'

const props = defineProps({
    vehicles: {
        type: Array,
        default: () => []
    },
    recentInspections: {
        type: Array,
        default: () => []
    }
})

const inUseVehicles = computed(() => {
    return props.vehicles.filter(vehicle => {
        return String(vehicle.status).toLowerCase() === 'in use'
    })
})

const availableVehicles = computed(() => {
    return props.vehicles.filter(vehicle => {
        return String(vehicle.status).toLowerCase() === 'available'
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
        <header class="bg-gray-200 px-6 py-5">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        src="/images/shipping-saint-logo.png"
                        alt="Shipping Saint"
                        class="h-14 w-auto"
                    />
                    <h1 class="text-3xl font-semibold">Vehicle Dashboard</h1>
                </div>

                <nav class="grid gap-3 sm:grid-cols-4">
                    <a href="/dashboard" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Home
                    </a>
                    <a href="/vehicles" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Manage Vehicles
                    </a>
                    <a href="/inspections/pre-trip" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Pre Trip Inspection
                    </a>
                    <a href="/inspections/post-trip" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Post Trip Inspection
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl p-6">
        <div class="mb-8 grid gap-6 md:grid-cols-2">
            <section class="rounded-lg border bg-green-50 p-4">
                <h2 class="mb-4 text-2xl font-semibold">Available</h2>

                <div v-if="availableVehicles.length" class="space-y-3">
                    <div v-for="vehicle in availableVehicles" :key="vehicle.id" class="rounded-lg border p-4">
                        <div class="text-xl font-semibold">{{ vehicle.name }}</div>
                        <div>Mileage: {{ vehicle.current_mileage }}</div>
                        <div>Status: {{ vehicle.status }}</div>
                    </div>
                </div>

                <p v-else>No vehicles are available.</p>
            </section>

            <section class="rounded-lg border bg-pink-50 p-4">
                <h2 class="mb-4 text-2xl font-semibold">In Use</h2>

                <div v-if="inUseVehicles.length" class="space-y-3">
                    <div v-for="vehicle in inUseVehicles" :key="vehicle.id" class="rounded-lg border p-4">
                        <div class="text-xl font-semibold">{{ vehicle.name }}</div>
                        <div>Mileage: {{ vehicle.current_mileage }}</div>
                        <div>Status: {{ vehicle.status }}</div>
                    </div>
                </div>

                <p v-else>No vehicles are currently in use.</p>
            </section>
        </div>

        <h2 class="mb-3 text-2xl font-semibold">Recent Inspections</h2>

        <div class="space-y-3">
            <div v-for="inspection in recentInspections" :key="inspection.id" class="rounded-lg border p-4">
                <div>{{ inspection.inspection_type }}</div>
                <div>Submitted: {{ formatSubmittedAt(inspection.created_at) }}</div>
                <div>Vehicle: {{ inspection.vehicle?.name }}</div>
                <div>Driver: {{ inspection.driver_name }}</div>
                <div>Starting Mileage: {{ inspection.starting_mileage }}</div>
                <div v-if="inspection.ending_mileage">
                    Ending Mileage: {{ inspection.ending_mileage }}
                </div>
            </div>
        </div>
        </main>
    </div>
</template>
