<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  value: {
    type: [String, Number],
    default: ''
  },
  tone: {
    type: String,
    default: 'neutral'
  }
})

const toneClasses = computed(() => {
  if (props.tone === 'positive') return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
  if (props.tone === 'warning') return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
  if (props.tone === 'negative') return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
  return 'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-200'
})
</script>

<template>
  <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium" :class="toneClasses">
    <span class="uppercase tracking-wide text-[10px] text-neutral-500">{{ label }}</span>
    <span>{{ value }}</span>
  </span>
</template>
