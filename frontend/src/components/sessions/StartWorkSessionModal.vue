<template>
  <div class="backdrop" @click.self="close">
    <div class="modal">

      <header class="modal__header">
        <h2>Nova sessió de treball</h2>
        <button class="modal__close" @click="close">✕</button>
      </header>

      <section class="modal__body">
        <form @submit.prevent="onSubmit">

          <div class="form-group">
            <label for="item">Selecciona un item</label>
            <select id="item" v-model="form.itemId" required>
              <option disabled value="">— Selecciona —</option>
              <option 
                v-for="item in items" 
                :key="item.id" 
                :value="item.id"
              >
                {{ item.name }}
              </option>
            </select>
          </div>
        </form>
      </section>

    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  itemId: '',
  worker: '',
  start: ''
})

function close() {
  emit('close')
}

function onSubmit() {
  emit('submit', { ...form })
  close()
}
</script>

<style scoped>
.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  width: 100%;
  max-width: 420px;
}
.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal__close {
  background: none;
  border: none;
  cursor: pointer;
}
.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}
.modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: .5rem;
}
.btn {
  padding: .45rem .9rem;
  border-radius: 4px;
  cursor: pointer;
}
.btn--primary {
  background: #0070f3;
  color: white;
}
.btn--secondary {
  background: #e0e0e0;
}
</style>
