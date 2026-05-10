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

function formatMileage(value) {
    return Number(value || 0).toLocaleString()
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Vehicles" />

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
        <form @submit.prevent="submit" class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20 md:grid-cols-2">
            <input v-model="form.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Vehicle name" />

            <input v-model="form.plate_number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Plate number" />

            <input v-model="form.current_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Current mileage" />

            <input v-model="form.required_maintenance_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Required maintenance mileage" />

            <textarea v-model="form.notes" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400 md:col-span-2" placeholder="Notes"></textarea>

            <button class="rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 md:col-span-2">
                Add Vehicle
            </button>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xl font-semibold text-white">🚐 {{ vehicle.name }}</div>
                        <div class="mt-1 text-sm text-slate-400">Plate: {{ vehicle.plate_number || 'N/A' }}</div>
                    </div>
                    <span class="rounded-full border border-blue-400/30 bg-blue-500/10 px-2 py-1 text-xs font-semibold text-blue-200">{{ vehicle.status }}</span>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500">Mileage</div>
                        <div class="text-lg font-semibold text-white">{{ formatMileage(vehicle.current_mileage) }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Service Due</div>
                        <div class="text-lg font-semibold text-white">{{ formatMileage(vehicle.required_maintenance_mileage) }}</div>
                    </div>
                </div>
            </div>
        </div>
        </main>
    </div>
</template>
