<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import ContentDrawer from '@/components/Workspace/ContentDrawer.vue'
import FilterBar from '@/components/Workspace/FilterBar.vue'
import LinkTable from '@/components/Workspace/LinkTable.vue'
import TopicTable from '@/components/Workspace/TopicTable.vue'
import Toolbar from '@/components/Workspace/Toolbar.vue'
import { useGraphStore } from '@/stores/graph'
import { useProjectStore } from '@/stores/project'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const projectStore = useProjectStore()
const graphStore = useGraphStore()
const uiStore = useUiStore()

const projectId = computed(() => route.params.id)

const filters = computed(() => uiStore.filters)

const visibleNodeIds = computed(() => {
  const ids = new Set()

  const evaluate = (node) => {
    if (!node) return false
    const children = Array.isArray(node.children) ? node.children : []
    let childVisible = false
    children.forEach((child) => {
      if (evaluate(child)) {
        childVisible = true
      }
    })

    const matchesKind = filters.value.kind === 'all' || node.kind === filters.value.kind
    const matchesViability = filters.value.viability === 'all' || node.metrics?.viability === filters.value.viability
    const matchesGeo = Number(node.metrics?.geoScore ?? 0) >= Number(filters.value.minGeo ?? 0)
    const matches = matchesKind && matchesViability && matchesGeo

    if (matches || childVisible) {
      ids.add(node.id)
      return true
    }

    return false
  }

  graphStore.nodes.forEach((node) => evaluate(node))
  return ids
})

const hasNodes = computed(() => graphStore.nodes.length > 0)
const hasVisibleNodes = computed(() => visibleNodeIds.value.size > 0)
const showFilterMessage = computed(() => hasNodes.value && !hasVisibleNodes.value)

const showTopics = computed(() => uiStore.activeTab === 'topics')

const loadProject = async () => {
  if (!projectId.value) return
  await projectStore.fetchProject(projectId.value)
  await projectStore.fetchLinks(projectId.value)
}

onMounted(loadProject)

watch(projectId, () => {
  loadProject()
})

const handleExport = async (format) => {
  try {
    const data = await projectStore.exportProject({ id: projectId.value, format })
    const content = typeof data === 'string' ? data : JSON.stringify(data, null, 2)
    const mime = format === 'csv' ? 'text/csv' : 'application/json'
    const blob = new Blob([content], { type: mime })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `project-${projectId.value}.${format === 'zip-md' ? 'json' : format}`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error(error)
    window.alert('Unable to export project at this time.')
  }
}
</script>

<template>
  <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10">
    <Toolbar @export="handleExport" />
    <FilterBar />

    <div v-if="showTopics" class="overflow-hidden rounded-lg border border-neutral-200">
      <TopicTable :nodes="graphStore.nodes" :visible-ids="visibleNodeIds" />
      <div v-if="showFilterMessage" class="px-4 py-6 text-center text-sm text-neutral-500">
        No topics match the current filters.
      </div>
    </div>

    <div v-else>
      <LinkTable :links="projectStore.links" @update:links="(links) => (projectStore.links = links)" />
    </div>
  </div>
  <ContentDrawer />
</template>
