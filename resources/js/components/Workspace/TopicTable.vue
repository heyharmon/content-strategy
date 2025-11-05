<script setup>
import { computed } from 'vue'
import TableRow from '@/components/Workspace/TableRow.vue'
import { useGraphStore } from '@/stores/graph'
import { useUiStore } from '@/stores/ui'

const props = defineProps({
  nodes: {
    type: Array,
    default: () => []
  },
  depth: {
    type: Number,
    default: 0
  },
  visibleIds: {
    type: Object,
    default: () => new Set()
  }
})

const graphStore = useGraphStore()
const uiStore = useUiStore()

const showEmptyState = computed(() => props.depth === 0 && (!props.nodes || props.nodes.length === 0))
const mergeSourceId = computed(() => uiStore.mergeSourceId)

const rows = computed(() => {
  const list = props.nodes || []
  if (!(props.visibleIds instanceof Set)) {
    return list
  }
  if (props.visibleIds.size === 0) {
    return []
  }
  return list.filter((node) => props.visibleIds.has(node.id))
})

const handleMergeRequest = async (node) => {
  if (!uiStore.mergeSourceId) {
    uiStore.setMergeSource(node.id)
    window.alert('Select another topic to merge with "' + node.topic + '".')
    return
  }

  if (uiStore.mergeSourceId === node.id) {
    uiStore.setMergeSource(null)
    return
  }

  try {
    await graphStore.merge(uiStore.mergeSourceId, node.id)
  } finally {
    uiStore.setMergeSource(null)
  }
}
</script>

<template>
  <table class="w-full table-auto border-collapse text-left">
    <thead v-if="depth === 0" class="bg-neutral-100 text-xs font-semibold uppercase tracking-wide text-neutral-500">
      <tr>
        <th class="px-4 py-3">Topic</th>
        <th class="px-4 py-3">Type</th>
        <th class="px-4 py-3">MSV</th>
        <th class="px-4 py-3">CPC</th>
        <th class="px-4 py-3">Competition</th>
        <th class="px-4 py-3">Breadth</th>
        <th class="px-4 py-3">GEO</th>
        <th class="px-4 py-3">Viability</th>
        <th class="px-4 py-3">Actions</th>
      </tr>
    </thead>
    <tbody v-if="showEmptyState && !rows.length" class="bg-white">
      <tr>
        <td colspan="9" class="px-4 py-6 text-center text-sm text-neutral-500">
          No topics yet. Use the controls above to generate a structure.
        </td>
      </tr>
    </tbody>
    <template v-else>
      <TableRow
        v-for="node in rows"
        :key="node.id"
        :node="node"
        :depth="depth"
        :merge-source-id="mergeSourceId"
        :visible-ids="visibleIds"
        @request-merge="handleMergeRequest"
      />
    </template>
  </table>
</template>
