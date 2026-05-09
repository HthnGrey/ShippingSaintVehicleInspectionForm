<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
    vehicles: Array
})

const form = useForm({
    name: '',
    plate_number: '',
    current_mileage: '',
    required_maintenance_mileage: '',
    notes: ''
})

function submit() {
    form.post(route('vehicles.store'), {
        onSuccess: () => form.reset()
    })
}
</script>

<template>
    <div>
        <AppHeader title="Vehicles" />

        <main class="mx-auto max-w-5xl p-6">
        <form @submit.prevent="submit" class="mb-8 space-y-4 rounded-lg border p-4">
            <input v-model="form.name" class="w-full rounded border p-2" placeholder="Vehicle name" />

            <input v-model="form.plate_number" class="w-full rounded border p-2" placeholder="Plate number" />

            <input v-model="form.current_mileage" type="number" class="w-full rounded border p-2" placeholder="Current mileage" />

            <input v-model="form.required_maintenance_mileage" type="number" class="w-full rounded border p-2" placeholder="Required maintenance mileage" />

            <textarea v-model="form.notes" class="w-full rounded border p-2" placeholder="Notes"></textarea>

            <button class="rounded bg-blue-600 px-4 py-2 text-white">
                Add Vehicle
            </button>
        </form>

        <div class="space-y-3">
            <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-lg border p-4">
                <div class="text-xl font-semibold">{{ vehicle.name }}</div>
                <div>Plate: {{ vehicle.plate_number || 'N/A' }}</div>
                <div>Mileage: {{ vehicle.current_mileage }}</div>
                <div>Required Maintenance Mileage: {{ vehicle.required_maintenance_mileage }}</div>
                <div>Status: {{ vehicle.status }}</div>
            </div>
        </div>
        </main>
    </div>
</template>
