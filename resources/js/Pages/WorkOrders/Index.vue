<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { router } from '@inertiajs/vue3'

defineProps({
    vehicles: {
        type: Array,
        default: () => []
    }
})

function completeMaintenance(vehicle) {
    router.post(route('work-orders.complete', vehicle.id), {}, {
        preserveScroll: true
    })
}
</script>

<template>
    <div>
        <AppHeader title="Work Orders" />

        <main class="mx-auto max-w-5xl p-6">
            <div v-if="vehicles.length" class="space-y-3">
                <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-lg border p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-xl font-semibold">{{ vehicle.name }}</div>
                            <div>Plate: {{ vehicle.plate_number || 'N/A' }}</div>
                            <div>Current Mileage: {{ vehicle.current_mileage }}</div>
                            <div>Required Maintenance Mileage: {{ vehicle.required_maintenance_mileage }}</div>
                            <div>Status: {{ vehicle.status }}</div>
                        </div>

                        <button
                            type="button"
                            class="rounded bg-blue-600 px-4 py-2 text-white"
                            @click="completeMaintenance(vehicle)"
                        >
                            Verify Maintenance Complete
                        </button>
                    </div>
                </div>
            </div>

            <p v-else>No work orders are currently open.</p>
        </main>
    </div>
</template>
