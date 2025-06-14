&lt;template>
  &lt;div class="h-full w-full">
    &lt;div v-if="error" class="text-red-600 p-4">
      {{ error }}
    &lt;/div>
    &lt;div v-else-if="loading" class="flex items-center justify-center h-full">
      Loading dashboard...
    &lt;/div>
    &lt;div v-else ref="embedContainer" class="h-full w-full">&lt;/div>
  &lt;/div>
&lt;/template>

&lt;script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { powerbi } from 'powerbi-client'
import { powerBiScopes } from '@/auth/authConfig'
import type { models } from 'powerbi-client'
import { PowerBI } from 'powerbi-client'

const props = defineProps&lt;{
  reportId: string
  workspaceId: string
}>()

const embedContainer = ref&lt;HTMLElement | null>(null)
const error = ref&lt;string | null>(null)
const loading = ref(true)
const authStore = useAuthStore()

onMounted(async () => {
  try {
    loading.value = true
    error.value = null

    const token = await authStore.getToken(powerBiScopes.scopes)
    if (!token) {
      throw new Error('Failed to get Power BI access token')
    }

    const powerbi = new PowerBI()
    
    const embedConfig = {
      type: 'report',
      tokenType: models.TokenType.Aad,
      accessToken: token,
      embedUrl: `https://app.powerbi.com/reportEmbed?reportId=${props.reportId}&groupId=${props.workspaceId}`,
      id: props.reportId,
      permissions: models.Permissions.All,
      settings: {
        panes: {
          filters: {
            expanded: false,
            visible: true
          }
        }
      }
    }

    if (embedContainer.value) {
      const report = powerbi.embed(embedContainer.value, embedConfig)
      
      report.on('loaded', () => {
        loading.value = false
      })

      report.on('error', (event) => {
        error.value = event.detail.message
        loading.value = false
      })
    }
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to load Power BI dashboard'
    loading.value = false
  }
})
&lt;/script>

&lt;style scoped>
.h-full {
  height: 100%;
}

.w-full {
  width: 100%;
}
&lt;/style>
