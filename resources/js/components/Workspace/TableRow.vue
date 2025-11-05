<script setup>
import { computed, defineAsyncComponent, ref, watch } from 'vue'
import MetricBadge from '@/components/Common/MetricBadge.vue'
import QualityBar from '@/components/Common/QualityBar.vue'
import { useGraphStore } from '@/stores/graph'
import { useUiStore } from '@/stores/ui'

const TopicTable = defineAsyncComponent(() => import('@/components/Workspace/TopicTable.vue'))

const props = defineProps({
  node: {
    type: Object,
    required: true
  },
  depth: {
    type: Number,
    default: 0
  },
  mergeSourceId: {
    type: String,
    default: null
  },
  visibleIds: {
    type: Object,
    default: () => new Set()
  }
})

const emit = defineEmits(['request-merge'])

const graphStore = useGraphStore()
const uiStore = useUiStore()

const localTopic = ref(props.node.topic)
const localKeyword = ref(props.node.primaryKeyword)

watch(
  () => props.node.topic,
  (value) => {
    localTopic.value = value
  }
)

watch(
  () => props.node.primaryKeyword,
  (value) => {
    localKeyword.value = value
  }
)

watch(localTopic, (value) => {
  graphStore.updateNode(props.node.id, { topic: value })
})

watch(localKeyword, (value) => {
  graphStore.updateNode(props.node.id, { primaryKeyword: value })
})

const hasChildren = computed(() => (props.node.children?.length || 0) > 0)
const isExpanded = computed(() => {
  if (uiStore.expanded[props.node.id] === undefined) {
    return props.depth === 0
  }
  return uiStore.expanded[props.node.id]
})

const indentation = computed(() => ({
  paddingLeft: `${props.depth * 1.5}rem`
}))

const isMergeSource = computed(() => props.mergeSourceId === props.node.id)

const viabilityTone = computed(() => {
  const value = props.node.metrics?.viability
  if (value === 'high') return 'positive'
  if (value === 'low') return 'negative'
  if (value === 'med') return 'warning'
  return 'neutral'
})

const viabilityLabel = computed(() => {
  const value = props.node.metrics?.viability
  if (value === 'high') return 'High'
  if (value === 'low') return 'Low'
  if (value === 'med') return 'Medium'
  return 'Unknown'
})

const toggleExpand = () => {
  uiStore.toggleRow(props.node.id)
}

const openDrawer = () => {
  uiStore.toggleDrawer(true, props.node.id)
}

const randomizeMetrics = () => {
  const metrics = {
    msv: Math.floor(Math.random() * 5000) + 100,
    cpc: Number((Math.random() * 8).toFixed(2)),
    competition: Number(Math.random().toFixed(2)),
    breadthScore: Number(Math.random().toFixed(2)),
    geoScore: Number(Math.random().toFixed(2)),
    viability: ['low', 'med', 'high'][Math.floor(Math.random() * 3)]
  }
  graphStore.updateMetrics(props.node.id, metrics)
}

const handlePromote = () => {
  graphStore.promote(props.node.id)
}

const handleDemote = () => {
  graphStore.demote(props.node.id)
}

const handleSplit = () => {
  graphStore.split(props.node.id)
}

const handleAddChild = () => {
  graphStore.addChild(props.node.id)
}

const handleMerge = () => {
  emit('request-merge', props.node)
}

const handleRegenerateBrief = () => {
  graphStore.regenerateBrief(props.node.id)
}
</script>

<template>
  <tbody>
    <tr class="border-b border-neutral-200 bg-white" :class="{ 'bg-amber-50': isMergeSource }">
      <td class="w-[280px] py-3 text-sm text-neutral-900" :style="indentation">
        <div class="flex items-center gap-2">
          <button
            v-if="hasChildren"
            type="button"
            class="rounded border border-neutral-300 px-1 text-xs"
            @click="toggleExpand"
          >
            {{ isExpanded ? '▼' : '▶' }}
          </button>
          <div class="flex-1 space-y-1">
            <input
              v-model="localTopic"
              type="text"
              class="w-full rounded-md border border-transparent px-2 py-1 text-sm focus:border-neutral-300 focus:outline-none"
            />
            <input
              v-model="localKeyword"
              type="text"
              class="w-full rounded-md border border-transparent px-2 py-1 text-xs text-neutral-500 focus:border-neutral-300 focus:outline-none"
            />
          </div>
        </div>
      </td>
      <td class="py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ node.kind }}</td>
      <td class="py-3 text-sm text-neutral-700">{{ node.metrics?.msv?.toLocaleString?.() ?? node.metrics?.msv }}</td>
      <td class="py-3 text-sm text-neutral-700">${{ node.metrics?.cpc }}</td>
      <td class="py-3 text-sm text-neutral-700">{{ node.metrics?.competition }}</td>
      <td class="py-3 text-sm text-neutral-700">{{ node.metrics?.breadthScore }}</td>
      <td class="py-3">
        <QualityBar :score="node.metrics?.geoScore ?? 0" label="GEO" />
      </td>
      <td class="py-3">
        <MetricBadge label="Viability" :value="viabilityLabel" :tone="viabilityTone" />
      </td>
      <td class="py-3">
        <div class="flex flex-wrap gap-2 text-xs">
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="openDrawer"
          >
            Edit Brief
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handlePromote"
          >
            Promote
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handleDemote"
          >
            Demote
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handleSplit"
          >
            Split
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handleAddChild"
          >
            + Add Spoke
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handleMerge"
          >
            Merge
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="handleRegenerateBrief"
          >
            Regenerate Brief
          </button>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-neutral-600 hover:bg-neutral-100"
            @click="randomizeMetrics"
          >
            Refresh Metrics
          </button>
        </div>
      </td>
    </tr>
    <tr v-if="hasChildren && isExpanded">
      <td colspan="9" class="bg-neutral-50 p-0">
        <TopicTable
          :nodes="node.children"
          :depth="depth + 1"
          :merge-source-id="mergeSourceId"
          :visible-ids="visibleIds"
          @request-merge="emit('request-merge', $event)"
        />
      </td>
    </tr>
  </tbody>
</template>
