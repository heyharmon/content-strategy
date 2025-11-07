<script setup>
import { computed } from 'vue'
import { useGraphStore } from '@/stores/graph'
import { useProjectStore } from '@/stores/project'
import { useUiStore } from '@/stores/ui'

const emit = defineEmits(['export'])

const graphStore = useGraphStore()
const projectStore = useProjectStore()
const uiStore = useUiStore()

const metadata = computed(() => projectStore.metadata || {})
const nodeCount = computed(() => graphStore.flattened.length)
const activeTab = computed(() => uiStore.activeTab)

const switchTab = (tab) => {
  uiStore.setTab(tab)
}

const addHub = () => {
  graphStore.addChild(null)
}

const exportFormat = (format) => {
  emit('export', format)
}
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
    <div>
      <h1 class="text-xl font-semibold text-neutral-900">
        {{ metadata.seed ? `Project: ${metadata.seed}` : 'Topic Workspace' }}
      </h1>
      <p class="text-sm text-neutral-600">
        Locale: {{ metadata.locale || 'en-US' }} · Depth: {{ metadata.depth || 3 }} levels · Nodes: {{ nodeCount }}
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div class="inline-flex rounded-md border border-neutral-200 p-1 text-sm">
        <button
          type="button"
          :class="[
            'rounded-md px-3 py-1 font-medium',
            activeTab === 'topics' ? 'bg-neutral-900 text-white' : 'text-neutral-600'
          ]"
          @click="switchTab('topics')"
        >
          Topic Structure
        </button>
        <button
          type="button"
          :class="[
            'rounded-md px-3 py-1 font-medium',
            activeTab === 'links' ? 'bg-neutral-900 text-white' : 'text-neutral-600'
          ]"
          @click="switchTab('links')"
        >
          Internal Links
        </button>
      </div>
      <button
        type="button"
        class="rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
        @click="addHub"
      >
        + Add Hub
      </button>
      <div class="inline-flex items-center gap-2">
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="exportFormat('json')"
        >
          Export JSON
        </button>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="exportFormat('csv')"
        >
          Export CSV
        </button>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="exportFormat('zip-md')"
        >
          Export Markdown
        </button>
      </div>
    </div>
  </div>
</template>
