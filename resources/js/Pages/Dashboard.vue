<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import DriveTimeWidget from '@/Components/DriveTimeWidget.vue'
import { computed } from 'vue'

const props = defineProps({
    vehicles: {
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

const maintenanceVehicles = computed(() => {
    return props.vehicles.filter(vehicle => {
        const status = String(vehicle.status).toLowerCase()

        return status === 'maintenance required' || status === 'needs maintenance'
    })
})

</script>

<template>
    <div>
        <AppHeader title="Vehicle Dashboard" />

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

        <section class="mx-auto mb-8 max-w-xl rounded-lg border bg-yellow-50 p-4">
            <h2 class="mb-4 text-center text-2xl font-semibold">Needs Maintenance</h2>

            <div v-if="maintenanceVehicles.length" class="space-y-3">
                <div v-for="vehicle in maintenanceVehicles" :key="vehicle.id" class="rounded-lg border p-4">
                    <div class="text-xl font-semibold">{{ vehicle.name }}</div>
                    <div>Mileage: {{ vehicle.current_mileage }}</div>
                    <div v-if="vehicle.required_maintenance_mileage">
                        Required Maintenance Mileage: {{ vehicle.required_maintenance_mileage }}
                    </div>
                    <div>Status: {{ vehicle.status }}</div>
                </div>
            </div>

            <p v-else class="text-center">No vehicles need maintenance.</p>
        </section>

        <DriveTimeWidget />
        </main>
    </div>
</template>
