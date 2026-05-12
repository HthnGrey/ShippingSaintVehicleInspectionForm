<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import InputError from '@/Components/InputError.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    vehicles: Array
})

const fuelLevels = ['Empty', '1/4', '1/2', '3/4', 'Full']
const page = usePage()
const driverName = computed(() => page.props.auth?.user?.name || '')

const form = useForm({
    vehicle_id: '',
    driver_name: driverName.value,
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
    form.driver_name = driverName.value
    form.post(route('inspections.pre.store'))
}

function cannotPreTrip(vehicle) {
    return ['In Use', 'Maintenance Required', 'Needs Maintenance'].includes(vehicle.status)
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Pre Trip Inspection" />

        <main class="mx-auto max-w-xl px-4 py-6 lg:ml-72 lg:px-8">
        <form @submit.prevent="submit" class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
            <select v-model="form.vehicle_id" @change="vehicleChanged" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400">
                <option value="">Select vehicle</option>
                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id" :disabled="cannotPreTrip(vehicle)">
                    {{ vehicle.name }} | {{ vehicle.status }}
                </option>
            </select>
            <InputError :message="form.errors.vehicle_id" />

            <div class="rounded-xl border border-slate-700 bg-slate-800 p-3 text-slate-300">
                <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Driver</div>
                <div class="mt-1 text-base font-semibold text-slate-100">{{ driverName }}</div>
            </div>

            <input v-model="form.starting_mileage" type="number" readonly class="w-full rounded-xl border border-slate-700 bg-slate-800 p-3 text-slate-300" />

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

            <textarea v-model="form.damage_notes" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Damage notes"></textarea>

            <button class="w-full rounded-xl bg-blue-600 p-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500">
                Submit Pre Trip
            </button>
        </form>
        </main>
    </div>
</template>
