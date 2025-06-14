&lt;template>
  &lt;div class="min-h-screen bg-gray-100">
    &lt;header class="bg-white shadow">
      &lt;div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        &lt;div class="flex items-center justify-between">
          &lt;h1 class="text-3xl font-bold text-gray-900">Weather Information&lt;/h1>
          &lt;router-link 
            to="/" 
            class="text-blue-600 hover:text-blue-800"
          >
            Back to Home
          &lt;/router-link>
        &lt;/div>
      &lt;/div>
    &lt;/header>
    
    &lt;main>
      &lt;div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        &lt;div class="px-4 py-6 sm:px-0">
          &lt;AuthRequired>
            &lt;div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              &lt;WeatherWidget 
                city="Lafia"
                :apiKey="import.meta.env.VITE_OPENWEATHER_API_KEY"
                class="h-full"
              />
              
              &lt;div class="space-y-6">
                &lt;WeatherWidget 
                  lat="8.5"
                  lon="8.5"
                  :apiKey="import.meta.env.VITE_OPENWEATHER_API_KEY"
                />
                
                &lt;WeatherWidget 
                  city="Keffi"
                  :apiKey="import.meta.env.VITE_OPENWEATHER_API_KEY"
                />
              &lt;/div>
            &lt;/div>
          &lt;/AuthRequired>
        &lt;/div>
      &lt;/div>
    &lt;/main>
  &lt;/div>
&lt;/template>

&lt;script setup lang="ts">
import { useHead } from '@vueuse/head'
import AuthRequired from '../components/AuthRequired.vue'
import WeatherWidget from '../components/WeatherWidget.vue'

useHead({
  title: 'Weather - NSG MOA',
  meta: [
    { name: 'description', content: 'Weather information for key locations in Nassarawa State' }
  ]
})
&lt;/script>
