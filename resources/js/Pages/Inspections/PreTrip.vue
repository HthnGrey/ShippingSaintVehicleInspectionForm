<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    vehicles: Array
})

const form = useForm({
    vehicle_id: '',
    driver_name: '',
    starting_mileage: '',
    fuel_level: '',
    tires_ok: false,
    lights_ok: false,
    brakes_ok: false,
    fluids_ok: false,
    damage_found: false,
    damage_notes: ''
})

function vehicleChanged() {
    const selectedVehicle = props.vehicles.find(
        vehicle => vehicle.id === Number(form.vehicle_id)
    )

    if (selectedVehicle) {
        form.starting_mileage = selectedVehicle.current_mileage
    }
}

function submit() {
    form.post(route('inspections.pre.store'))
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
                    <h1 class="text-3xl font-semibold">Pre Trip Inspection</h1>
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

        <main class="mx-auto max-w-xl p-6">
        <form @submit.prevent="submit" class="space-y-4">
            <select v-model="form.vehicle_id" @change="vehicleChanged" class="w-full rounded border p-3">
                <option value="">Select vehicle</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                    {{ vehicle.name }} | {{ vehicle.status }}
                </option>
            </select>

            <input v-model="form.driver_name" class="w-full rounded border p-3" placeholder="Driver name" />

            <input v-model="form.starting_mileage" type="number" readonly class="w-full rounded border bg-gray-100 p-3" />

            <input v-model="form.fuel_level" class="w-full rounded border p-3" placeholder="Fuel level" />

            <div class="grid grid-cols-3 gap-x-6 gap-y-3">
                <label class="flex items-center gap-2"><input v-model="form.tires_ok" type="checkbox" /> Tires OK</label>
                <label class="flex items-center gap-2"><input v-model="form.lights_ok" type="checkbox" /> Lights OK</label>
                <label class="flex items-center gap-2"><input v-model="form.brakes_ok" type="checkbox" /> Brakes OK</label>
                <label class="flex items-center gap-2"><input v-model="form.fluids_ok" type="checkbox" /> Fluids OK</label>
                <label class="flex items-center gap-2"><input v-model="form.damage_found" type="checkbox" /> Damage Found</label>
            </div>

            <textarea v-model="form.damage_notes" class="w-full rounded border p-3" placeholder="Damage notes"></textarea>

            <button class="w-full rounded bg-blue-600 p-3 text-white">
                Submit Pre Trip
            </button>
        </form>
        </main>
    </div>
</template>
