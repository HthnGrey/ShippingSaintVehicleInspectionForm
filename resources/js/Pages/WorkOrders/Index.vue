<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
    vehicles: {
        type: Array,
        default: () => []
    }
})

const selectedVehicle = ref(null)
const form = useForm({
    completed_by: '',
    maintenance_completed: '',
})

function openMaintenanceModal(vehicle) {
    selectedVehicle.value = vehicle
    form.clearErrors()
    form.reset()
}

function closeMaintenanceModal() {
    selectedVehicle.value = null
    form.clearErrors()
    form.reset()
}

function submitMaintenance() {
    if (!selectedVehicle.value) {
        return
    }

    form.post(route('work-orders.complete', selectedVehicle.value.id), {
        preserveScroll: true,
        onSuccess: () => closeMaintenanceModal(),
    })
}

function formatMileage(value) {
    return Number(value || 0).toLocaleString()
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Work Orders" />

        <main class="mx-auto max-w-6xl px-4 py-6 lg:ml-72 lg:px-8">
            <div v-if="vehicles.length" class="grid gap-4 lg:grid-cols-2">
                <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-2xl border border-amber-400/30 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-xl font-semibold text-white">🛠 {{ vehicle.name }}</div>
                            <div class="mt-2 text-sm text-slate-400">Plate: {{ vehicle.plate_number || 'N/A' }}</div>
                            <div class="text-sm text-slate-300">Current Mileage: {{ formatMileage(vehicle.current_mileage) }}</div>
                            <div class="text-sm text-amber-200">Required Maintenance Mileage: {{ formatMileage(vehicle.required_maintenance_mileage) }}</div>
                            <div class="mt-3 inline-flex rounded-full border border-amber-400/40 bg-amber-500/10 px-2 py-1 text-xs font-semibold text-amber-200">{{ vehicle.status }}</div>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500"
                            @click="openMaintenanceModal(vehicle)"
                        >
                            Verify Maintenance Complete
                        </button>
                    </div>
                </div>
            </div>

            <p v-else class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 text-slate-400 shadow-xl shadow-black/20">No work orders are currently open.</p>
        </main>

        <div v-if="selectedVehicle" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl shadow-black/40">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">Maintenance Verification</p>
                        <h2 class="mt-1 text-2xl font-semibold text-white">{{ selectedVehicle.name }}</h2>
                        <p class="mt-1 text-sm text-slate-400">Submission time and date will be recorded automatically.</p>
                    </div>
                    <button type="button" class="rounded-xl border border-slate-700 px-3 py-2 text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeMaintenanceModal">
                        Close
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitMaintenance">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300" for="completed_by">Name</label>
                        <input
                            id="completed_by"
                            v-model="form.completed_by"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                            placeholder="Who completed the maintenance?"
                        />
                        <p v-if="form.errors.completed_by" class="mt-2 text-sm text-red-300">{{ form.errors.completed_by }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300" for="maintenance_completed">Maintenance Completed</label>
                        <textarea
                            id="maintenance_completed"
                            v-model="form.maintenance_completed"
                            class="min-h-36 w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                            placeholder="Describe the maintenance performed"
                        ></textarea>
                        <p v-if="form.errors.maintenance_completed" class="mt-2 text-sm text-red-300">{{ form.errors.maintenance_completed }}</p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="rounded-xl border border-slate-700 px-4 py-2 font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeMaintenanceModal">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Submitting...' : 'Submit Verification' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
