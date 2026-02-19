<template>
  <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left: Logo + Navigation -->
        <div class="flex items-center gap-8">
          <!-- Logo -->
          <router-link to="/projects" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">H</span>
            </div>
            <span class="font-bold text-xl text-gray-900 hidden sm:block">HobbyPlanner</span>
          </router-link>

          <!-- Main Navigation -->
          <div class="hidden md:flex items-center gap-1">
            <NavLink to="/projects" icon="📊">
              Projects
            </NavLink>
          </div>
        </div>

        <!-- Right: User Menu -->
        <div class="flex items-center gap-4">
          <!-- Quick Add Button -->
          <button
            class="hidden md:flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Quick Add
          </button>

          <!-- User Menu -->
          <UserMenu />
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import NavLink from './NavLink.vue'
import UserMenu from './UserMenu.vue'
</script>
