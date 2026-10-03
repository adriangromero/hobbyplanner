import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useTimerStore } from '@/stores/timerStore'
import './assets/style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
useTimerStore(pinia).restorePaintingTimerFromStorage()
app.use(router)
app.mount('#app')
