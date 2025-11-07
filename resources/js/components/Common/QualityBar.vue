<script setup>
import { computed } from 'vue'

const props = defineProps({
  score: {
    type: Number,
    default: 0
  },
  label: {
    type: String,
    default: ''
  }
})

const percentage = computed(() => {
  const value = Number.isFinite(props.score) ? props.score : 0
  return Math.min(100, Math.max(0, Math.round(value * 100)))
})

const barColor = computed(() => {
  if (percentage.value >= 70) return 'bg-emerald-500'
  if (percentage.value >= 40) return 'bg-amber-500'
  return 'bg-rose-500'
})
</script>

<template>
  <div class="space-y-1">
    <div class="flex items-center justify-between text-xs text-neutral-500">
      <span>{{ label }}</span>
      <span>{{ percentage }}%</span>
    </div>
    <div class="h-2 w-full rounded-full bg-neutral-200">
      <div
        class="h-2 rounded-full transition-all"
        :class="barColor"
        :style="{ width: `${percentage}%` }"
      ></div>
    </div>
  </div>
</template>
