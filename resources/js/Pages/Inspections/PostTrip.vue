<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import InputError from '@/Components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    vehicles: Array,
    preTripInspections: {
        type: Array,
        default: () => []
    }
})

const fuelLevels = ['Empty', '1/4', '1/2', '3/4', 'Full']

const form = useForm({
    vehicle_id: '',
    driver_name: '',
    starting_mileage: '',
    ending_mileage: '',
    fuel_level: '',
    tires_ok: false,
    lights_ok: false,
    brakes_ok: false,
    fluids_ok: false,
    damage_found: false,
    damage_notes: ''
})

const selectedPreTrip = computed(() => {
    return preTripForVehicle(Number(form.vehicle_id))
})

function preTripForVehicle(vehicleId) {
    return props.preTripInspections.find(preTrip => preTrip.vehicle_id === vehicleId)
}

function vehicleChanged() {
    const selectedVehicle = props.vehicles.find(
        vehicle => vehicle.id === Number(form.vehicle_id)
    )
    const preTrip = selectedPreTrip.value

    if (preTrip) {
        form.driver_name = preTrip.driver_name
        form.starting_mileage = preTrip.starting_mileage
        form.fuel_level = preTrip.fuel_level ?? ''
        return
    }

    if (selectedVehicle) {
        form.starting_mileage = selectedVehicle.current_mileage
    }
}

function submit() {
    form.post(route('inspections.post.store'))
}

function cannotPostTrip(vehicle) {
    return vehicle.status === 'Available'
}

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
        <AppHeader title="Post Trip Inspection" />

        <main class="mx-auto max-w-xl p-6">
        <form @submit.prevent="submit" class="space-y-4">
            <select v-model="form.vehicle_id" @change="vehicleChanged" class="w-full rounded border p-3">
                <option value="">Select vehicle</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id" :disabled="cannotPostTrip(vehicle)">
                    {{ vehicle.name }} | {{ vehicle.status }}
                </option>
            </select>
            <InputError :message="form.errors.vehicle_id" />

            <section v-if="selectedPreTrip" class="rounded-lg border bg-gray-50 p-4">
                <h2 class="mb-3 text-xl font-semibold">Paired Pre Trip</h2>
                <div>Submitted: {{ formatSubmittedAt(selectedPreTrip.created_at) }}</div>
                <div>Driver: {{ selectedPreTrip.driver_name }}</div>
                <div>Starting Mileage: {{ selectedPreTrip.starting_mileage }}</div>
                <div v-if="selectedPreTrip.fuel_level">Fuel Level: {{ selectedPreTrip.fuel_level }}</div>
                <div v-if="selectedPreTrip.damage_notes">Damage Notes: {{ selectedPreTrip.damage_notes }}</div>
            </section>

            <input
                v-model="form.driver_name"
                :readonly="Boolean(selectedPreTrip)"
                class="w-full rounded border p-3"
                :class="{ 'bg-gray-100': selectedPreTrip }"
                placeholder="Driver name"
            />

            <input v-model="form.starting_mileage" type="number" readonly class="w-full rounded border bg-gray-100 p-3" />

            <input v-model="form.ending_mileage" type="number" class="w-full rounded border p-3" placeholder="Ending mileage" />

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
                Submit Post Trip
            </button>
        </form>
        </main>
    </div>
</template>
