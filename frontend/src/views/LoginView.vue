<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-md w-full max-w-md p-8">

      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">HobbyPlanner</h1>
        <p class="text-gray-500 text-sm mt-2">
          Gestiona tus proyectos de hobby. Inicia sesión o regístrate para comenzar.
        </p>
      </div>

      <!-- Tabs -->
      <div class="flex mb-6 border-b">
        <button
          @click="mode = 'login'"
          :class="mode === 'login'
            ? 'border-b-2 border-red-600 text-red-600'
            : 'text-gray-500 hover:text-gray-700'"
          class="flex-1 pb-3 text-sm font-medium transition"
        >
          Iniciar sesión
        </button>
        <button
          @click="mode = 'register'"
          :class="mode === 'register'
            ? 'border-b-2 border-red-600 text-red-600'
            : 'text-gray-500 hover:text-gray-700'"
          class="flex-1 pb-3 text-sm font-medium transition"
        >
          Registrarse
        </button>
      </div>

      <!-- Error -->
      <div v-if="store.error" class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg">
        {{ store.error }}
      </div>

      <!-- Login Form -->
      <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="tu@email.com"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="••••••••"
          />
        </div>
        <button
          type="submit"
          :disabled="store.loading"
          class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 rounded-lg transition disabled:opacity-50"
        >
          {{ store.loading ? 'Cargando...' : 'Entrar' }}
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input
            v-model="name"
            type="text"
            required
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="Tu nombre"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="tu@email.com"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="••••••••"
          />
        </div>
        <button
          type="submit"
          :disabled="store.loading"
          class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 rounded-lg transition disabled:opacity-50"
        >
          {{ store.loading ? 'Cargando...' : 'Registrarse' }}
        </button>
      </form>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const store  = useAuthStore()
const router = useRouter()

const mode     = ref<'login' | 'register'>('login')
const email    = ref('')
const password = ref('')
const name     = ref('')

async function handleLogin() {
  try {
    await store.login(email.value, password.value)
    router.push('/projects')
  } catch {
    // store.error ya se muestra en el template
  }
}

async function handleRegister() {
  try {
    await store.register(email.value, password.value, name.value)
    // Auto-login tras registro exitoso
    await store.login(email.value, password.value)
    router.push('/projects')
  } catch {
    // store.error ya se muestra en el template
  }
}
</script>