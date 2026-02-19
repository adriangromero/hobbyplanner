<template>
  <table class="w-full bg-white rounded-lg shadow-sm border">
    <thead class="bg-gray-100 text-left">
      <tr>
        <th class="p-3">Name</th>
        <th class="p-3">Estimated Hours</th>
      </tr>
    </thead>

    <tbody>
      <ItemRow 
        v-for="item in items"
        :key="item.id"
        :item="item"
      />
    </tbody>
  </table>
</template>

<script setup>
import ItemRow from './ItemRow.vue'

defineProps({
  items: {
    type: Array,
    default: () => []
  }
})
</script>
