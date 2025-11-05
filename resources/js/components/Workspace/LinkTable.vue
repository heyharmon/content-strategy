<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  links: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:links'])

const threshold = ref(0.4)
const localLinks = ref([])

watch(
  () => props.links,
  (value) => {
    localLinks.value = (value || []).map((item) => ({ ...item, approved: item.approved ?? false }))
  },
  { immediate: true }
)

const filtered = computed(() =>
  localLinks.value.filter((link) => Number(link.confidence ?? 0) >= threshold.value)
)

const toggleApprove = (link) => {
  link.approved = !link.approved
  emit('update:links', localLinks.value)
}

const bulkApprove = () => {
  localLinks.value = localLinks.value.map((item) => ({ ...item, approved: true }))
  emit('update:links', localLinks.value)
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-semibold text-neutral-900">Internal Linking Map</h2>
        <p class="text-sm text-neutral-600">Review and approve suggested anchor text across the hub and spoke model.</p>
      </div>
      <div class="flex items-center gap-3">
        <label class="text-sm text-neutral-600">
          Confidence ≥
          <input
            v-model.number="threshold"
            type="number"
            min="0"
            max="1"
            step="0.05"
            class="ml-2 w-24 rounded-md border border-neutral-300 px-3 py-1 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="bulkApprove"
        >
          Approve All
        </button>
      </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white">
      <table class="min-w-full table-auto text-left">
        <thead class="bg-neutral-100 text-xs font-semibold uppercase tracking-wide text-neutral-500">
          <tr>
            <th class="px-4 py-3">From Topic</th>
            <th class="px-4 py-3">To Topic</th>
            <th class="px-4 py-3">Direction</th>
            <th class="px-4 py-3">Anchor Text</th>
            <th class="px-4 py-3">Confidence</th>
            <th class="px-4 py-3">Approve</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!filtered.length">
            <td colspan="6" class="px-4 py-6 text-center text-sm text-neutral-500">No link suggestions match this filter.</td>
          </tr>
          <tr v-for="link in filtered" :key="`${link.from}-${link.to}`" class="border-b border-neutral-100">
            <td class="px-4 py-3 text-sm text-neutral-700">{{ link.from }}</td>
            <td class="px-4 py-3 text-sm text-neutral-700">{{ link.to }}</td>
            <td class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-neutral-500">{{ link.direction }}</td>
            <td class="px-4 py-3 text-sm text-neutral-700">
              <input
                v-model="link.anchorText"
                type="text"
                class="w-full rounded-md border border-neutral-200 px-2 py-1 text-sm focus:border-neutral-900 focus:outline-none"
              />
            </td>
            <td class="px-4 py-3 text-sm text-neutral-700">{{ (link.confidence ?? 0).toFixed(2) }}</td>
            <td class="px-4 py-3">
              <label class="inline-flex items-center gap-2 text-sm text-neutral-600">
                <input type="checkbox" v-model="link.approved" @change="toggleApprove(link)" />
                <span>{{ link.approved ? 'Approved' : 'Pending' }}</span>
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
