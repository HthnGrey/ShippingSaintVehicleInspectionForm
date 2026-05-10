<script setup>
import { computed, ref } from 'vue'

const accessToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN || ''
const startLocation = ref('')
const endLocation = ref('')
const startLocationLabel = ref('')
const endLocationLabel = ref('')
const distance = ref('')
const duration = ref('')
const routeGeometry = ref(null)
const routeOrigin = ref(null)
const routeDestination = ref(null)
const errorMessage = ref('')
const statusMessage = ref('')
const isLoading = ref(false)

const routeMapUrl = computed(() => {
    if (!accessToken || !routeGeometry.value || !routeOrigin.value || !routeDestination.value) {
        return ''
    }

    const routeOverlay = encodeURIComponent(JSON.stringify({
        type: 'Feature',
        properties: {
            stroke: '#3b82f6',
            'stroke-width': 5,
            'stroke-opacity': 0.95,
        },
        geometry: routeGeometry.value,
    }))
    const originMarker = `pin-s-a+22c55e(${routeOrigin.value.longitude},${routeOrigin.value.latitude})`
    const destinationMarker = `pin-s-b+3b82f6(${routeDestination.value.longitude},${routeDestination.value.latitude})`
    const overlays = [
        `geojson(${routeOverlay})`,
        originMarker,
        destinationMarker,
    ].join(',')

    return `https://api.mapbox.com/styles/v1/mapbox/dark-v11/static/${overlays}/auto/1000x360?padding=70&access_token=${accessToken}`
})

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
        geometries: 'geojson',
        overview: 'simplified',
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
    routeGeometry.value = null
    routeOrigin.value = null
    routeDestination.value = null

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
        routeGeometry.value = route.geometry
        routeOrigin.value = origin
        routeDestination.value = destination
        statusMessage.value = ''
    } catch (error) {
        errorMessage.value = error.message || 'Unable to calculate drive time.'
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <section class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-white">Drive Time</h2>
            <p class="text-sm text-slate-400">Enter a start and end location.</p>
        </div>

        <form class="grid gap-3 lg:grid-cols-[1fr_1fr_auto]" @submit.prevent="calculateDriveTime">
            <input
                v-model="startLocation"
                class="rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                placeholder="Start location"
            />
            <input
                v-model="endLocation"
                class="rounded-xl border border-slate-700 bg-slate-950 p-3 text-slate-100 placeholder:text-slate-500 focus:border-blue-400 focus:ring-blue-400"
                placeholder="End location"
            />
            <button
                type="submit"
                class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:opacity-60"
                :disabled="isLoading"
            >
                {{ isLoading ? 'Calculating...' : 'Calculate Drive Time' }}
            </button>
        </form>

        <p v-if="statusMessage" class="mt-3 text-sm text-slate-400">{{ statusMessage }}</p>
        <p v-if="errorMessage" class="mt-3 text-sm text-red-300">{{ errorMessage }}</p>

        <div v-if="duration" class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                <div class="text-sm text-slate-500">Start Location</div>
                <div class="text-lg font-semibold text-white">{{ startLocationLabel }}</div>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                <div class="text-sm text-slate-500">End Location</div>
                <div class="text-lg font-semibold text-white">{{ endLocationLabel }}</div>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                <div class="text-sm text-slate-500">Estimated Drive Time</div>
                <div class="text-2xl font-semibold text-white">{{ duration }}</div>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                <div class="text-sm text-slate-500">Distance</div>
                <div class="text-2xl font-semibold text-white">{{ distance }}</div>
            </div>
        </div>

        <div v-if="routeMapUrl" class="mt-4 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/70">
            <img
                :src="routeMapUrl"
                :alt="`Route map from ${startLocationLabel} to ${endLocationLabel}`"
                class="h-72 w-full object-cover"
            />
        </div>
    </section>
</template>
