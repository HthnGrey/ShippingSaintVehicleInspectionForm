<script setup>
import { ref } from 'vue'

const accessToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN || ''
const startLocation = ref('')
const endLocation = ref('')
const startLocationLabel = ref('')
const endLocationLabel = ref('')
const distance = ref('')
const duration = ref('')
const errorMessage = ref('')
const statusMessage = ref('')
const isLoading = ref(false)

async function geocodeAddress(address, fallbackLabel) {
    if (!accessToken) {
        throw new Error('Mapbox access token is not configured.')
    }

    const params = new URLSearchParams({
        access_token: accessToken,
        limit: '1',
    })
    const encodedAddress = encodeURIComponent(address)
    const response = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodedAddress}.json?${params.toString()}`)
    const result = await response.json()

    if (!response.ok || result.message) {
        throw new Error(result.message || `Mapbox could not find ${fallbackLabel}.`)
    }

    const location = result.features?.[0]
    const [longitude, latitude] = location?.center || []

    if (!latitude || !longitude) {
        throw new Error(`Mapbox could not find ${fallbackLabel}.`)
    }

    return {
        latitude,
        longitude,
        label: location.place_name || address,
    }
}

async function routeToDestination(origin, destination) {
    const coordinates = [
        `${origin.longitude},${origin.latitude}`,
        `${destination.longitude},${destination.latitude}`,
    ].join(';')
    const params = new URLSearchParams({
        access_token: accessToken,
        overview: 'false',
    })
    const response = await fetch(`https://api.mapbox.com/directions/v5/mapbox/driving/${coordinates}?${params.toString()}`)
    const result = await response.json()

    if (!response.ok || result.code !== 'Ok' || !result.routes?.[0]) {
        throw new Error('Unable to calculate a route to that destination.')
    }

    return result.routes[0]
}

function formatDuration(seconds) {
    const minutes = Math.round(seconds / 60)
    const hours = Math.floor(minutes / 60)
    const remainingMinutes = minutes % 60

    if (!hours) {
        return `${minutes} min`
    }

    if (!remainingMinutes) {
        return `${hours} hr`
    }

    return `${hours} hr ${remainingMinutes} min`
}

function formatDistance(meters) {
    return `${(meters / 1609.344).toFixed(1)} mi`
}

async function calculateDriveTime() {
    errorMessage.value = ''
    statusMessage.value = ''
    startLocationLabel.value = ''
    endLocationLabel.value = ''
    distance.value = ''
    duration.value = ''

    if (!startLocation.value.trim()) {
        errorMessage.value = 'Enter a start location.'
        return
    }

    if (!endLocation.value.trim()) {
        errorMessage.value = 'Enter an end location.'
        return
    }

    isLoading.value = true
    statusMessage.value = 'Finding locations...'

    try {
        const origin = await geocodeAddress(startLocation.value, 'the start location')
        const destination = await geocodeAddress(endLocation.value, 'the end location')
        startLocationLabel.value = origin.label
        endLocationLabel.value = destination.label

        statusMessage.value = 'Calculating drive time...'
        const route = await routeToDestination(origin, destination)

        duration.value = formatDuration(route.duration)
        distance.value = formatDistance(route.distance)
        statusMessage.value = ''
    } catch (error) {
        errorMessage.value = error.message || 'Unable to calculate drive time.'
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <section class="mt-8 rounded-lg border p-4">
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-2xl font-semibold">Drive Time</h2>
            <p class="text-sm text-gray-600">Enter a start and end location.</p>
        </div>

        <form class="grid gap-3 lg:grid-cols-[1fr_1fr_auto]" @submit.prevent="calculateDriveTime">
            <input
                v-model="startLocation"
                class="rounded border p-3"
                placeholder="Start location"
            />
            <input
                v-model="endLocation"
                class="rounded border p-3"
                placeholder="End location"
            />
            <button
                type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-60"
                :disabled="isLoading"
            >
                {{ isLoading ? 'Calculating...' : 'Calculate Drive Time' }}
            </button>
        </form>

        <p v-if="statusMessage" class="mt-3 text-sm text-gray-600">{{ statusMessage }}</p>
        <p v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</p>

        <div v-if="duration" class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded border bg-gray-50 p-3">
                <div class="text-sm text-gray-600">Start Location</div>
                <div class="text-lg font-semibold">{{ startLocationLabel }}</div>
            </div>
            <div class="rounded border bg-gray-50 p-3">
                <div class="text-sm text-gray-600">End Location</div>
                <div class="text-lg font-semibold">{{ endLocationLabel }}</div>
            </div>
            <div class="rounded border bg-gray-50 p-3">
                <div class="text-sm text-gray-600">Estimated Drive Time</div>
                <div class="text-2xl font-semibold">{{ duration }}</div>
            </div>
            <div class="rounded border bg-gray-50 p-3">
                <div class="text-sm text-gray-600">Distance</div>
                <div class="text-2xl font-semibold">{{ distance }}</div>
            </div>
        </div>
    </section>
</template>
