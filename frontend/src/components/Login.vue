&lt;template>
  &lt;div class="flex items-center justify-center min-h-[400px] w-full">
    &lt;div v-if="!authStore.isLoggedIn" class="text-center">
      &lt;h2 class="text-2xl font-bold mb-4">Welcome to NSG MOA Dashboard</h2>
      &lt;p class="mb-6">Please sign in to access the dashboard</p>
      &lt;button
        @click="login"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        :disabled="loading"
      >
        {{ loading ? 'Signing in...' : 'Sign in with Microsoft' }}
      &lt;/button>
      &lt;p v-if="authStore.error" class="mt-4 text-red-600">
        {{ authStore.error }}
      &lt;/p>
    &lt;/div>
    &lt;div v-else class="text-center">
      &lt;p class="mb-4">Welcome, {{ authStore.currentUser?.name }}!&lt;/p>
      &lt;button
        @click="logout"
        class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors"
        :disabled="loading"
      >
        {{ loading ? 'Signing out...' : 'Sign out' }}
      &lt;/button>
    &lt;/div>
  &lt;/div>
&lt;/template>

&lt;script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const loading = ref(false)

const login = async () => {
  loading.value = true
  try {
    await authStore.login()
  } finally {
    loading.value = false
  }
}

const logout = async () => {
  loading.value = true
  try {
    await authStore.logout()
  } finally {
    loading.value = false
  }
}
&lt;/script>
