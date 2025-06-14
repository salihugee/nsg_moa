<template>
  <div class="h-[600px] w-full">
    <l-map
      v-model="zoom"
      v-model:center="center"
      :use-global-leaflet="false"
      class="h-full w-full"
    >
      <l-tile-layer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        layer-type="base"
        name="OpenStreetMap"
      />
      <l-marker
        v-for="marker in markers"
        :key="marker.id"
        :lat-lng="marker.position"
        @click="handleMarkerClick(marker)"
      >
        <l-popup>
          <div>
            <h3 class="font-bold">{{ marker.title }}</h3>
            <p>{{ marker.description }}</p>
          </div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'

// Nasarawa State coordinates
const center = ref([8.5, 8.0])
const zoom = ref(8)

interface MapMarker {
  id: number
  position: [number, number]
  title: string
  description: string
}

const markers = ref<MapMarker[]>([
  {
    id: 1,
    position: [8.5, 8.0],
    title: 'Agricultural Zone A',
    description: 'Major farming region with focus on yam and cassava production'
  }
  // Add more markers as needed
])

const handleMarkerClick = (marker: MapMarker) => {
  console.log('Marker clicked:', marker)
}
</script>

<style>
@import 'leaflet/dist/leaflet.css';
</style>
