<script setup>
import AppHeader from '@/Components/AppHeader.vue'

defineProps({
    auditLogs: {
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
        <AppHeader title="Audit Logs" />

        <main class="mx-auto max-w-6xl px-4 py-6 lg:ml-72 lg:px-8">
            <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-white">Recent Activity</h2>
                    <span class="rounded-full border border-blue-400/30 bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-200">{{ auditLogs.length }} entries</span>
                </div>

                <div v-if="auditLogs.length" class="space-y-3">
                    <article v-for="log in auditLogs" :key="log.id" class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-300">{{ log.action }}</div>
                                <div class="mt-1 text-base font-semibold text-white">{{ log.description }}</div>
                                <div class="mt-1 text-sm text-slate-400">By {{ log.user?.name || 'System' }}</div>
                            </div>
                            <div class="text-sm text-slate-500">{{ formatSubmittedAt(log.created_at) }}</div>
                        </div>
                    </article>
                </div>

                <p v-else class="rounded-xl border border-slate-800 bg-slate-950/70 p-5 text-sm text-slate-400">
                    No activity has been recorded yet.
                </p>
            </section>
        </main>
    </div>
</template>
