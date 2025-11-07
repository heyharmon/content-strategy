<script setup>
import { computed } from 'vue'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const filters = computed(() => uiStore.filters)

const updateFilter = (payload) => {
  uiStore.setFilters(payload)
}
</script>

<template>
  <div class="flex flex-wrap items-center gap-4 rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
    <label class="flex flex-col text-sm text-neutral-600">
      <span class="mb-1 font-medium text-neutral-700">Viability</span>
      <select
        :value="filters.viability"
        class="w-40 rounded-md border border-neutral-300 px-3 py-2 focus:border-neutral-900 focus:outline-none"
        @change="updateFilter({ viability: $event.target.value })"
      >
        <option value="all">All</option>
        <option value="high">High</option>
        <option value="med">Medium</option>
        <option value="low">Low</option>
      </select>
    </label>

    <label class="flex flex-col text-sm text-neutral-600">
      <span class="mb-1 font-medium text-neutral-700">Topic Type</span>
      <select
        :value="filters.kind"
        class="w-40 rounded-md border border-neutral-300 px-3 py-2 focus:border-neutral-900 focus:outline-none"
        @change="updateFilter({ kind: $event.target.value })"
      >
        <option value="all">All</option>
        <option value="hub">Hubs</option>
        <option value="spoke">Spokes</option>
        <option value="subspoke">Sub-Spokes</option>
      </select>
    </label>

    <div class="flex flex-col text-sm text-neutral-600">
      <span class="mb-1 font-medium text-neutral-700">Minimum GEO Score</span>
      <input
        :value="filters.minGeo"
        type="range"
        min="0"
        max="1"
        step="0.05"
        class="w-48 accent-neutral-900"
        @input="updateFilter({ minGeo: Number($event.target.value) })"
      />
      <span class="text-xs text-neutral-500">{{ Number(filters.minGeo).toFixed(2) }}</span>
    </div>

    <button
      type="button"
      class="rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
      @click="uiStore.resetFilters()"
    >
      Reset Filters
    </button>
  </div>
</template>
