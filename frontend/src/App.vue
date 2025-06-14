<script setup lang="ts">
import { ref } from 'vue'
import { BellIcon } from '@heroicons/vue/outline'
import LanguageSelector from './components/LanguageSelector.vue'

const navigationItems = ref([
  { name: 'Home', path: '/' },
  { name: 'Agricultural Directory', path: '/directory' },
  { name: 'Data Explorer', path: '/explorer' },
  { name: 'Investment', path: '/investment' },
  { name: 'About', path: '/about' }
])
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <router-link to="/" class="flex-shrink-0 flex items-center">
              <img class="h-8 w-auto" src="/logo.png" alt="NSG MOA" />
            </router-link>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link 
                v-for="item in navigationItems" 
                :key="item.path"
                :to="item.path"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-indigo-500 text-gray-900"
              >
                {{ item.name }}
              </router-link>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <button 
              type="button" 
              class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">View notifications</span>
              <BellIcon class="h-6 w-6" aria-hidden="true" />
            </button>
            <LanguageSelector />
          </div>
        </div>
      </div>
    </nav>

    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
