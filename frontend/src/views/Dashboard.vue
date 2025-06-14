&lt;template>
  &lt;div class="min-h-screen bg-gray-100">
    &lt;header class="bg-white shadow">
      &lt;div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        &lt;div class="flex items-center justify-between">
          &lt;h1 class="text-3xl font-bold text-gray-900">Dashboard&lt;/h1>
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
            &lt;div class="bg-white rounded-lg shadow h-[600px]">
              &lt;PowerBIDashboard 
                :reportId="import.meta.env.VITE_POWERBI_REPORT_ID"
                :workspaceId="import.meta.env.VITE_POWERBI_WORKSPACE_ID"
              />
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
import PowerBIDashboard from '../components/PowerBIDashboard.vue'

useHead({
  title: 'Dashboard - NSG MOA',
  meta: [
    { name: 'description', content: 'Interactive Power BI Dashboard for Nassarawa State GIS and Ministry of Agriculture' }
  ]
})
&lt;/script>
