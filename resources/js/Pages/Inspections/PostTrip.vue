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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Post Trip Inspection" />

        <main class="mx-auto max-w-xl px-4 py-6 lg:ml-72 lg:px-8">
        <form @submit.prevent="submit" class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
            <select v-model="form.vehicle_id" @change="vehicleChanged" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400">
                <option value="">Select vehicle</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id" :disabled="cannotPostTrip(vehicle)">
                    {{ vehicle.name }} | {{ vehicle.status }}
                </option>
            </select>
            <InputError :message="form.errors.vehicle_id" />

            <section v-if="selectedPreTrip" class="rounded-xl border border-blue-400/30 bg-blue-500/10 p-4 text-slate-300">
                <h2 class="mb-3 text-xl font-semibold text-white">Paired Pre Trip</h2>
                <div>Submitted: {{ formatSubmittedAt(selectedPreTrip.created_at) }}</div>
                <div>Driver: {{ selectedPreTrip.driver_name }}</div>
                <div>Starting Mileage: {{ selectedPreTrip.starting_mileage }}</div>
                <div v-if="selectedPreTrip.fuel_level">Fuel Level: {{ selectedPreTrip.fuel_level }}</div>
                <div v-if="selectedPreTrip.damage_notes">Damage Notes: {{ selectedPreTrip.damage_notes }}</div>
            </section>

            <input
                v-model="form.driver_name"
                :readonly="Boolean(selectedPreTrip)"
                class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                :class="{ 'bg-slate-800 text-slate-300': selectedPreTrip }"
                placeholder="Driver name"
            />

            <input v-model="form.starting_mileage" type="number" readonly class="w-full rounded-xl border border-slate-700 bg-slate-800 p-3 text-slate-300" />

            <input v-model="form.ending_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Ending mileage" />

            <select v-model="form.fuel_level" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400">
                <option value="">Select fuel level</option>
                <option v-for="fuelLevel in fuelLevels" :key="fuelLevel" :value="fuelLevel">
                    {{ fuelLevel }}
                </option>
            </select>

            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm text-slate-300 sm:grid-cols-3">
                <label class="flex items-center gap-2"><input v-model="form.tires_ok" type="checkbox" /> Tires OK</label>
                <label class="flex items-center gap-2"><input v-model="form.lights_ok" type="checkbox" /> Lights OK</label>
                <label class="flex items-center gap-2"><input v-model="form.brakes_ok" type="checkbox" /> Brakes OK</label>
                <label class="flex items-center gap-2"><input v-model="form.fluids_ok" type="checkbox" /> Fluids OK</label>
                <label class="flex items-center gap-2"><input v-model="form.damage_found" type="checkbox" /> Damage Found</label>
            </div>

            <textarea
                v-model="form.damage_notes"
                class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                :required="form.damage_found"
                :placeholder="form.damage_found ? 'Damage notes required' : 'Damage notes'"
            ></textarea>
            <InputError :message="form.errors.damage_notes" />

            <button class="w-full rounded-xl bg-blue-600 p-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500">
                Submit Post Trip
            </button>
        </form>
        </main>
    </div>
</template>
