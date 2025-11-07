<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: ''
  },
  confirmLabel: {
    type: String,
    default: 'Confirm'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const handleBackdrop = (event) => {
  if (event.target === event.currentTarget) {
    emit('cancel')
  }
}
</script>

<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center bg-black/40" @click="handleBackdrop">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.stop>
        <h3 class="text-lg font-semibold text-neutral-900">{{ title }}</h3>
        <p class="mt-2 text-sm text-neutral-600">{{ message }}</p>
        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
            @click="emit('cancel')"
          >
            {{ cancelLabel }}
          </button>
          <button
            type="button"
            class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white hover:bg-neutral-800"
            @click="emit('confirm')"
          >
            {{ confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
