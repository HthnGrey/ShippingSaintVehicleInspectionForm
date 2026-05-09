<script setup>
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    title: {
        type: String,
        required: true
    }
})
</script>

<template>
    <header class="bg-gray-200 px-6 py-5">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <img
                    src="/images/shipping-saint-logo.png"
                    alt="Shipping Saint"
                    class="h-14 w-auto"
                />
                <h1 class="text-3xl font-semibold">{{ title }}</h1>
            </div>

            <nav class="grid gap-3 sm:grid-cols-4">
                <Link :href="route('dashboard')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                    Home
                </Link>
                <Link :href="route('vehicles.index')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                    Manage Vehicles
                </Link>
                <Link :href="route('work-orders.index')" class="rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                    Work Orders
                </Link>
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button type="button" class="w-full rounded bg-white px-4 py-2 text-center text-sm font-medium shadow-sm">
                            Inspection
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('inspections.index')">
                            Recent Inspections
                        </DropdownLink>
                        <DropdownLink :href="route('inspections.pre')">
                            Pre Trip Inspection
                        </DropdownLink>
                        <DropdownLink :href="route('inspections.post')">
                            Post Trip Inspection
                        </DropdownLink>
                    </template>
                </Dropdown>
            </nav>
        </div>
    </header>
</template>
