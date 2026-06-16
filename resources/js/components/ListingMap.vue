<script setup lang="ts">
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import 'leaflet/dist/leaflet.css';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    latitude?: number | string | null;
    longitude?: number | string | null;
    title?: string;
}>();

const { t } = useI18n();

const mapContainer = ref<HTMLElement | null>(null);
const map = ref<L.Map | null>(null);
const marker = ref<L.Marker | null>(null);

const isValidCoordinate = (val: any) => {
    return val !== null && val !== undefined && val !== 0 && val !== '0' && val !== '';
};

const hasCoordinates = ref(isValidCoordinate(props.latitude) && isValidCoordinate(props.longitude));

onMounted(() => {
    if (!hasCoordinates.value || !mapContainer.value) {
return;
}

    const lat = Number(props.latitude);
    const lng = Number(props.longitude);

    // Fix for default Leaflet marker icons in Vue/Vite
    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    map.value = L.map(mapContainer.value).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    marker.value = L.marker([lat, lng]).addTo(map.value);
    
    if (props.title) {
        marker.value.bindPopup(`<b>${props.title}</b>`);
    }
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
        map.value = null;
    }
});

watch(() => [props.latitude, props.longitude], () => {
    const valid = isValidCoordinate(props.latitude) && isValidCoordinate(props.longitude);
    hasCoordinates.value = valid;
    
    if (valid && map.value) {
        const lat = Number(props.latitude);
        const lng = Number(props.longitude);
        map.value.setView([lat, lng], 15);

        if (marker.value) {
            marker.value.setLatLng([lat, lng]);
        }
    }
});
</script>

<template>
    <div v-if="hasCoordinates" class="w-full">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <slot name="icon"></slot>
                {{ t('location_on_map') }}
            </h2>
            <a 
                :href="`https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`" 
                target="_blank" 
                rel="noopener noreferrer"
                class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
            >
                {{ t('view_on_google_maps') }}
            </a>
        </div>
        <div class="relative w-full h-[400px] rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm" style="z-index: 1;">
            <div ref="mapContainer" class="absolute inset-0 w-full h-full"></div>
        </div>
    </div>
</template>
