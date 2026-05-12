<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import InputError from '@/Components/InputError.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    inspection: {
        type: Object,
        required: true,
    },
})

const fuelLevels = ['Empty', '1/4', '1/2', '3/4', 'Full']
const isPostTrip = props.inspection.inspection_type === 'Post Trip'

const form = useForm({
    driver_name: props.inspection.driver_name ?? '',
    starting_mileage: props.inspection.starting_mileage ?? '',
    ending_mileage: props.inspection.ending_mileage ?? '',
    fuel_level: props.inspection.fuel_level ?? '',
    tires_ok: Boolean(props.inspection.tires_ok),
    lights_ok: Boolean(props.inspection.lights_ok),
    brakes_ok: Boolean(props.inspection.brakes_ok),
    fluids_ok: Boolean(props.inspection.fluids_ok),
    damage_found: Boolean(props.inspection.damage_found),
    damage_notes: props.inspection.damage_notes ?? '',
})

function submit() {
    form.patch(route('inspections.update', props.inspection.id))
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader :title="`Edit ${inspection.inspection_type} Inspection`" />

        <main class="mx-auto max-w-xl px-4 py-6 lg:ml-72 lg:px-8">
            <form @submit.prevent="submit" class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 text-sm text-slate-300">
                    <div class="text-lg font-semibold text-white">{{ inspection.vehicle?.name || 'Vehicle' }}</div>
                    <div>Inspection Type: {{ inspection.inspection_type }}</div>
                    <div v-if="inspection.pre_trip">Paired Pre Trip: {{ inspection.pre_trip.vehicle?.name || inspection.vehicle?.name }}</div>
                </div>

                <div>
                    <input v-model="form.driver_name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Driver name" />
                    <InputError :message="form.errors.driver_name" />
                </div>

                <div>
                    <input v-model="form.starting_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Starting mileage" />
                    <InputError :message="form.errors.starting_mileage" />
                </div>

                <div v-if="isPostTrip">
                    <input v-model="form.ending_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Ending mileage" />
                    <InputError :message="form.errors.ending_mileage" />
                </div>

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

                <div>
                    <textarea
                        v-model="form.damage_notes"
                        class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                        :required="isPostTrip && form.damage_found"
                        :placeholder="isPostTrip && form.damage_found ? 'Damage notes required' : 'Damage notes'"
                    ></textarea>
                    <InputError :message="form.errors.damage_notes" />
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Link :href="route('inspections.index')" class="rounded-xl border border-slate-700 px-4 py-2 text-center font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white">
                        Cancel
                    </Link>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
