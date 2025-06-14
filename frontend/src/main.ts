import { createApp } from 'vue'
import { createHead } from '@vueuse/head'
import App from './App.vue'
import router from './router'
import pinia from './stores'
import './assets/main.css'

const app = createApp(App)
const head = createHead()

app.use(router)
app.use(pinia)
app.use(head)

app.mount('#app')
