<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import InputError from '@/Components/InputError.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    vehicles: Array
})

const fuelLevels = ['Empty', '1/4', '1/2', '3/4', 'Full']

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

function cannotPreTrip(vehicle) {
    return vehicle.status === 'In Use'
}
</script>

<template>
    <div>
        <AppHeader title="Pre Trip Inspection" />

        <main class="mx-auto max-w-xl p-6">
        <form @submit.prevent="submit" class="space-y-4">
            <select v-model="form.vehicle_id" @change="vehicleChanged" class="w-full rounded border p-3">
                <option value="">Select vehicle</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id" :disabled="cannotPreTrip(vehicle)">
                    {{ vehicle.name }} | {{ vehicle.status }}
                </option>
            </select>
            <InputError :message="form.errors.vehicle_id" />

            <input v-model="form.driver_name" class="w-full rounded border p-3" placeholder="Driver name" />

            <input v-model="form.starting_mileage" type="number" readonly class="w-full rounded border bg-gray-100 p-3" />

            <select v-model="form.fuel_level" class="w-full rounded border p-3">
                <option value="">Select fuel level</option>
                <option v-for="fuelLevel in fuelLevels" :key="fuelLevel" :value="fuelLevel">
                    {{ fuelLevel }}
                </option>
            </select>

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
