<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineProps({
    maintenanceReports: {
        type: Array,
        default: () => []
    }
})

const selectedReport = ref(null)
const page = usePage()
const permissions = computed(() => page.props.auth?.permissions || {})
const form = useForm({
    completed_by: '',
    maintenance_completed: '',
    completed_at: '',
})

function formatSubmittedAt(value) {
    if (!value) {
        return 'Not available'
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}

function formatDateTimeLocal(value) {
    if (!value) {
        return ''
    }

    const date = new Date(value)
    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000)

    return offsetDate.toISOString().slice(0, 16)
}

function openEditReport(report) {
    selectedReport.value = report
    form.clearErrors()
    form.completed_by = report.completed_by ?? ''
    form.maintenance_completed = report.maintenance_completed ?? ''
    form.completed_at = formatDateTimeLocal(report.completed_at)
}

function closeEditReport() {
    selectedReport.value = null
    form.clearErrors()
    form.reset()
}

function submitReport() {
    if (!selectedReport.value) {
        return
    }

    form.patch(route('reports.update', selectedReport.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEditReport(),
    })
}

function deleteReport(report) {
    if (!window.confirm('Remove this maintenance report?')) {
        return
    }

    router.delete(route('reports.destroy', report.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Reports" />

        <main class="mx-auto max-w-7xl px-4 py-6 lg:ml-72 lg:px-8">
            <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">Maintenance History</p>
                        <h2 class="mt-1 text-2xl font-semibold text-white">Completed Maintenance Reports</h2>
                    </div>
                    <span class="rounded-full border border-blue-400/30 bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-200">
                        {{ maintenanceReports.length }} entries
                    </span>
                    <a :href="route('exports.reports')" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white">
                        Export CSV
                    </a>
                </div>

                <div v-if="maintenanceReports.length" class="space-y-4">
                    <article
                        v-for="report in maintenanceReports"
                        :key="report.id"
                        class="rounded-xl border border-slate-800 bg-slate-950/70 p-5"
                    >
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-white">🛠 {{ report.vehicle?.name || 'Vehicle' }}</h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    Submitted by {{ report.completed_by }} on {{ formatSubmittedAt(report.completed_at) }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Updated at {{ formatSubmittedAt(report.updated_at) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <template v-if="permissions.manageReports">
                                    <button type="button" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-200 transition hover:border-blue-400 hover:text-white" @click="openEditReport(report)">
                                        Edit
                                    </button>
                                    <button type="button" class="rounded-lg border border-red-400/40 px-3 py-2 text-sm font-semibold text-red-200 transition hover:border-red-300 hover:text-white" @click="deleteReport(report)">
                                        Remove
                                    </button>
                                </template>
                                <div class="rounded-full border border-green-400/30 bg-green-500/10 px-3 py-2 text-sm font-semibold text-green-200">
                                    Completed
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Maintenance Completed</div>
                            <p class="whitespace-pre-line text-sm leading-6 text-slate-200">{{ report.maintenance_completed }}</p>
                        </div>
                    </article>
                </div>

                <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">
                    No maintenance reports have been submitted.
                </p>
            </section>
        </main>

        <div v-if="selectedReport && permissions.manageReports" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl shadow-black/40">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">Edit Maintenance Report</p>
                        <h2 class="mt-1 text-2xl font-semibold text-white">{{ selectedReport.vehicle?.name || 'Vehicle' }}</h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Updated at {{ formatSubmittedAt(selectedReport.updated_at) }}
                        </p>
                    </div>
                    <button type="button" class="rounded-xl border border-slate-700 px-3 py-2 text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeEditReport">
                        Close
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitReport">
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
                        <label class="mb-2 block text-sm font-medium text-slate-300" for="completed_at">Completed At</label>
                        <input
                            id="completed_at"
                            v-model="form.completed_at"
                            type="datetime-local"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400"
                        />
                        <p v-if="form.errors.completed_at" class="mt-2 text-sm text-red-300">{{ form.errors.completed_at }}</p>
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
                        <p class="text-sm text-slate-500 sm:mr-auto sm:self-center">
                            Saving changes records a new updated time automatically.
                        </p>
                        <button type="button" class="rounded-xl border border-slate-700 px-4 py-2 font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" @click="closeEditReport">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
