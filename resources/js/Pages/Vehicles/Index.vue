<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineProps({
    vehicles: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions || {})
const selectedVehicle = ref(null)

const form = useForm({
    name: '',
    plate_number: '',
    current_mileage: '',
    required_maintenance_mileage: '',
    notes: ''
})

const editForm = useForm({
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

function openEdit(vehicle) {
    selectedVehicle.value = vehicle
    editForm.clearErrors()
    editForm.name = vehicle.name ?? ''
    editForm.plate_number = vehicle.plate_number ?? ''
    editForm.current_mileage = vehicle.current_mileage ?? ''
    editForm.required_maintenance_mileage = vehicle.required_maintenance_mileage ?? ''
    editForm.notes = vehicle.notes ?? ''
}

function closeEdit() {
    selectedVehicle.value = null
    editForm.clearErrors()
    editForm.reset()
}

function updateVehicle() {
    if (!selectedVehicle.value) {
        return
    }

    editForm.patch(route('vehicles.update', selectedVehicle.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEdit(),
    })
}

function deleteVehicle(vehicle) {
    if (!window.confirm(`Delete ${vehicle.name}?`)) {
        return
    }

    router.delete(route('vehicles.destroy', vehicle.id), {
        preserveScroll: true,
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
            <form v-if="permissions.createVehicles" @submit.prevent="submit" class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20 md:grid-cols-2">
                <input v-model="form.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Vehicle name" />
                <input v-model="form.plate_number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Plate number" />
                <input v-model="form.current_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Current mileage" />
                <input v-model="form.required_maintenance_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Required maintenance mileage" />
                <textarea v-model="form.notes" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400 md:col-span-2" placeholder="Notes"></textarea>

                <div v-if="Object.keys(form.errors).length" class="rounded-xl border border-red-400/30 bg-red-500/10 p-3 text-sm text-red-200 md:col-span-2">
                    <div v-for="(message, field) in form.errors" :key="field">{{ message }}</div>
                </div>

                <button class="rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 md:col-span-2">
                    Add Vehicle
                </button>
            </form>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="vehicle in vehicles" :key="vehicle.id" class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-semibold text-white">{{ vehicle.name }}</div>
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

                    <p v-if="vehicle.notes" class="mt-4 rounded-xl border border-slate-800 bg-slate-950/70 p-3 text-sm text-slate-300">{{ vehicle.notes }}</p>

                    <div v-if="permissions.updateVehicles || permissions.deleteVehicles" class="mt-5 flex flex-wrap gap-2">
                        <button v-if="permissions.updateVehicles" type="button" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white" @click="openEdit(vehicle)">
                            Edit
                        </button>
                        <button v-if="permissions.deleteVehicles" type="button" class="rounded-lg border border-red-400/40 px-3 py-2 text-sm font-semibold text-red-200 transition hover:border-red-300 hover:text-white" @click="deleteVehicle(vehicle)">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <div v-if="selectedVehicle && permissions.updateVehicles" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl shadow-black/40">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">Edit Vehicle</p>
                        <h2 class="mt-1 text-2xl font-semibold text-white">{{ selectedVehicle.name }}</h2>
                    </div>
                    <button type="button" class="rounded-xl border border-slate-700 px-3 py-2 text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeEdit">
                        Close
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="updateVehicle">
                    <input v-model="editForm.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Vehicle name" />
                    <input v-model="editForm.plate_number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Plate number" />
                    <input v-model="editForm.current_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Current mileage" />
                    <input v-model="editForm.required_maintenance_mileage" type="number" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Required maintenance mileage" />
                    <textarea v-model="editForm.notes" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Notes"></textarea>

                    <div v-if="Object.keys(editForm.errors).length" class="rounded-xl border border-red-400/30 bg-red-500/10 p-3 text-sm text-red-200">
                        <div v-for="(message, field) in editForm.errors" :key="field">{{ message }}</div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-700 px-4 py-2 font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeEdit">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60" :disabled="editForm.processing">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
