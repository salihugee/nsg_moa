<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to NSG MOA</h1>
      </div>
    </header>
    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
          <div class="bg-white rounded-lg shadow">
            <AuthRequired>
              <div class="p-6">
                <p class="text-lg text-center mb-8">
                  Welcome to the Nassarawa State GIS and Ministry of Agriculture Dashboard
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <router-link 
                    to="/dashboard" 
                    class="flex items-center justify-center p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                  >
                    <div class="text-center">
                      <h3 class="text-xl font-semibold mb-2">Power BI Dashboard</h3>
                      <p class="text-gray-600">View detailed analytics and reports</p>
                    </div>
                  </router-link>

                  <router-link 
                    to="/weather" 
                    class="flex items-center justify-center p-6 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                  >
                    <div class="text-center">
                      <h3 class="text-xl font-semibold mb-2">Weather Information</h3>
                      <p class="text-gray-600">Check current weather conditions</p>
                    </div>
                  </router-link>
                </div>
              </div>
            </AuthRequired>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@vueuse/head'
import AuthRequired from '../components/AuthRequired.vue'

useHead({
  title: 'Home - NSG MOA',
  meta: [
    { name: 'description', content: 'Home page of the Nassarawa State GIS and Ministry of Agriculture Dashboard' }
  ]
})
</script>
