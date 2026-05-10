<script setup>
import AppHeader from '@/Components/AppHeader.vue'

defineProps({
    maintenanceReports: {
        type: Array,
        default: () => []
    }
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
                            </div>

                            <div class="rounded-full border border-green-400/30 bg-green-500/10 px-3 py-1 text-sm font-semibold text-green-200">
                                Completed
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
    </div>
</template>
