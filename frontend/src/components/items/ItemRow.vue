<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="p-3">{{ item.name }}</td>
    <td class="p-3">{{ item.estimatedHours }}h</td>
  </tr>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  }
})
</script>
