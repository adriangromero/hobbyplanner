<template>
  <aside class="w-64 bg-gray-900 text-white p-4 flex flex-col gap-6">
    <h1 class="text-2xl font-bold">Hobby Planner</h1>

    <nav class="flex flex-col gap-3">
      <RouterLink
        to="/projects"
        class="hover:text-blue-400"
        active-class="text-blue-400"
      >
        Projects
      </RouterLink>
    </nav>
  </aside>
</template>
