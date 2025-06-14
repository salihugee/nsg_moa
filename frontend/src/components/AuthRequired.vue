&lt;template>
  &lt;div v-if="authStore.isLoggedIn">
    &lt;slot>&lt;/slot>
  &lt;/div>
  &lt;div v-else>
    &lt;Login />
  &lt;/div>
&lt;/template>

&lt;script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Login from './Login.vue'

const authStore = useAuthStore()

onMounted(async () => {
  await authStore.initialize()
})
&lt;/script>
