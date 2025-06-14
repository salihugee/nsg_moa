<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">About</h1>
      </div>
    </header>
    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
          <div class="prose lg:prose-xl">
            <h2>About NSG MOA</h2>
            <p>This is the about page for the Nassarawa State Ministry of Agriculture application.</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useHead } from '@vueuse/head'

useHead({
  title: 'About - NSG MOA',
  meta: [
    { name: 'description', content: 'About page of NSG MOA application' }
  ]
})
</script>
