&lt;template>
  &lt;div class="bg-white rounded-lg shadow p-4">
    &lt;div v-if="loading" class="flex items-center justify-center">
      Loading weather data...
    &lt;/div>
    &lt;div v-else-if="error" class="text-red-600">
      {{ error }}
    &lt;/div>
    &lt;div v-else-if="weather" class="flex flex-col">
      &lt;div class="flex items-center justify-between mb-4">
        &lt;h3 class="text-lg font-semibold">Weather in {{ weather.name }}&lt;/h3>
        &lt;button 
          @click="refreshWeather"
          class="text-blue-600 hover:text-blue-800"
          :disabled="loading"
        >
          Refresh
        &lt;/button>
      &lt;/div>
      
      &lt;div class="grid grid-cols-2 gap-4">
        &lt;div>
          &lt;p class="text-3xl font-bold">
            {{ Math.round(weather.main.temp) }}°C
          &lt;/p>
          &lt;p class="text-gray-600 capitalize">
            {{ weather.weather[0].description }}
          &lt;/p>
        &lt;/div>
        
        &lt;div class="space-y-2">
          &lt;p class="text-sm text-gray-600">
            Humidity: {{ weather.main.humidity }}%
          &lt;/p>
          &lt;p class="text-sm text-gray-600">
            Wind: {{ Math.round(weather.wind.speed * 3.6) }} km/h
          &lt;/p>
        &lt;/div>
      &lt;/div>
    &lt;/div>
  &lt;/div>
&lt;/template>

&lt;script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface WeatherData {
  name: string
  main: {
    temp: number
    humidity: number
  }
  weather: Array&lt;{
    description: string
    icon: string
  }>
  wind: {
    speed: number
  }
}

const props = defineProps&lt;{
  city?: string
  lat?: number
  lon?: number
  apiKey: string
}>()

const weather = ref&lt;WeatherData | null>(null)
const loading = ref(true)
const error = ref&lt;string | null>(null)

const fetchWeather = async () => {
  try {
    loading.value = true
    error.value = null
    
    let url = 'https://api.openweathermap.org/data/2.5/weather?'
    
    if (props.city) {
      url += `q=${encodeURIComponent(props.city)}`
    } else if (props.lat !== undefined && props.lon !== undefined) {
      url += `lat=${props.lat}&lon=${props.lon}`
    } else {
      throw new Error('Either city or coordinates must be provided')
    }
    
    url += `&appid=${props.apiKey}&units=metric`
    
    const response = await axios.get&lt;WeatherData>(url)
    weather.value = response.data
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to fetch weather data'
  } finally {
    loading.value = false
  }
}

const refreshWeather = () => {
  fetchWeather()
}

onMounted(() => {
  fetchWeather()
})
&lt;/script>
