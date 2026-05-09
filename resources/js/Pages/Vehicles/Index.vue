<script setup>
import { Link, useForm } from '@inertiajs/vue3'

defineProps({
    vehicles: Array
})

const form = useForm({
    name: '',
    plate_number: '',
    current_mileage: '',
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
        <header class="bg-gray-200 px-6 py-5">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        src="/images/shipping-saint-logo.png"
                        alt="Shipping Saint"
                        class="h-14 w-auto"
                    />
                    <h1 class="text-3xl font-semibold">Vehicles</h1>
                </div>

                <nav class="grid gap-3 sm:grid-cols-4">
                    <Link :href="route('dashboard')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Home
                    </Link>
                    <Link :href="route('vehicles.index')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Manage Vehicles
                    </Link>
                    <Link :href="route('inspections.pre')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Pre Trip Inspection
                    </Link>
                    <Link :href="route('inspections.post')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                        Post Trip Inspection
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl p-6">
        <form @submit.prevent="submit" class="mb-8 space-y-4 rounded-lg border p-4">
            <input v-model="form.name" class="w-full rounded border p-2" placeholder="Vehicle name" />

            <input v-model="form.plate_number" class="w-full rounded border p-2" placeholder="Plate number" />

            <input v-model="form.current_mileage" type="number" class="w-full rounded border p-2" placeholder="Current mileage" />

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
                <div>Status: {{ vehicle.status }}</div>
            </div>
        </div>
        </main>
    </div>
</template>
