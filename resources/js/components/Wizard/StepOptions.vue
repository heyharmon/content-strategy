<script setup>
import { useVModel } from '@vueuse/core'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const form = useVModel(props, 'modelValue', emit)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold text-neutral-900">Metric Filters</h2>
      <p class="mt-1 text-sm text-neutral-600">
        Define the minimum thresholds and structure depth for your topic cluster.
      </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Minimum Search Volume</span>
        <input
          v-model.number="form.constraints.minMsv"
          type="number"
          min="0"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Minimum CPC ($)</span>
        <input
          v-model.number="form.constraints.minCpc"
          type="number"
          step="0.1"
          min="0"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Depth</span>
        <select
          v-model.number="form.depth"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        >
          <option :value="2">2 Levels</option>
          <option :value="3">3 Levels</option>
        </select>
      </label>
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Max spokes per hub</span>
        <input
          v-model.number="form.maxSpokes"
          type="number"
          min="2"
          max="8"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>
    </div>
  </div>
</template>
