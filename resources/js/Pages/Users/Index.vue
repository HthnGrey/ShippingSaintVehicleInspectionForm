<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import InputError from '@/Components/InputError.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    users: {
        type: Array,
        default: () => []
    },
    roles: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    name: '',
    email: '',
    role: 'driver',
    password: '',
    password_confirmation: '',
})
const page = usePage()
const currentUserId = computed(() => page.props.auth?.user?.id)

function createUser() {
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email', 'password', 'password_confirmation'),
    })
}

function updateRole(user, role) {
    useForm({ role }).patch(route('users.update', user.id), {
        preserveScroll: true,
    })
}

function deleteUser(user) {
    if (user.id === currentUserId.value) {
        return
    }

    if (!window.confirm(`Delete ${user.name}?`)) {
        return
    }

    router.delete(route('users.destroy', user.id), {
        preserveScroll: true,
    })
}

function roleLabel(role) {
    return String(role).charAt(0).toUpperCase() + String(role).slice(1)
}

function formatDate(value) {
    if (!value) {
        return 'Not available'
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
    }).format(new Date(value))
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <AppHeader title="Users" />

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 lg:ml-72 lg:px-8">
            <form @submit.prevent="createUser" class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20 md:grid-cols-2">
                <div>
                    <input v-model="form.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <input v-model="form.email" type="email" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Email" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <select v-model="form.role" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 focus:border-blue-400 focus:ring-blue-400">
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ roleLabel(role) }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div>
                    <input v-model="form.password" type="password" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <input v-model="form.password_confirmation" type="password" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400" placeholder="Confirm password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <button class="rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create User' }}
                </button>
            </form>

            <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-white">Registered Users</h2>
                    <span class="rounded-full border border-blue-400/30 bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-200">{{ users.length }} users</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="border-b border-slate-800 text-xs uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="py-3 pr-4">Name</th>
                                <th class="py-3 pr-4">Email</th>
                                <th class="py-3 pr-4">Role</th>
                                <th class="py-3 pr-4">Created</th>
                                <th class="py-3 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="user in users" :key="user.id">
                                <td class="py-4 pr-4 font-semibold text-white">{{ user.name }}</td>
                                <td class="py-4 pr-4 text-slate-300">{{ user.email }}</td>
                                <td class="py-4 pr-4">
                                    <select :value="user.role" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-blue-400 focus:ring-blue-400" @change="updateRole(user, $event.target.value)">
                                        <option v-for="role in roles" :key="role" :value="role">
                                            {{ roleLabel(role) }}
                                        </option>
                                    </select>
                                </td>
                                <td class="py-4 pr-4 text-slate-400">{{ formatDate(user.created_at) }}</td>
                                <td class="py-4 pr-4">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-400/40 px-3 py-2 text-sm font-semibold text-red-200 transition hover:border-red-300 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="user.id === currentUserId"
                                        @click="deleteUser(user)"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</template>
