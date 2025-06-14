<template>
  <div class="relative">
    <button 
      type="button"
      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      @click="isOpen = !isOpen"
    >
      {{ currentLanguage }}
      <chevron-down-icon class="ml-2 h-4 w-4" />
    </button>

    <div 
      v-if="isOpen"
      class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
      role="menu"
      aria-orientation="vertical"
      aria-labelledby="language-menu"
    >
      <div class="py-1" role="none">
        <button
          v-for="lang in languages"
          :key="lang.code"
          class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          role="menuitem"
          @click="selectLanguage(lang.code)"
        >
          {{ lang.name }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/solid'

const isOpen = ref(false)
const selectedLanguage = ref('en')

const languages = [
  { code: 'en', name: 'English' },
  { code: 'ha', name: 'Hausa' }
]

const currentLanguage = computed(() => {
  const lang = languages.find(l => l.code === selectedLanguage.value)
  return lang ? lang.name : 'English'
})

const selectLanguage = (code: string) => {
  selectedLanguage.value = code
  isOpen.value = false
  // TODO: Implement language change logic
}
</script>
