import { defineStore } from 'pinia'
import api from '@/services/api'
import { useProjectStore } from '@/stores/project'

const defaultMetrics = () => ({
  msv: 0,
  cpc: 0,
  competition: 0,
  breadthScore: 0,
  geoScore: 0,
  viability: 'med'
})

const defaultBrief = (topic = 'New Topic', kind = 'spoke') => ({
  title: `${topic} Brief`,
  searchIntent: kind === 'hub' ? 'Informational' : 'Commercial',
  summary: 'Add a summary for this topic.',
  outline: ['Introduction', 'Key Points', 'Call to Action'],
  faqs: ['What should this cover?'],
  entities: [],
  geoPrompts: [],
  schemaHints: ['Article'],
  wordCountRange: [800, 1200]
})

const createLocalNode = (kind, parentId = null) => {
  const topic = kind === 'hub' ? 'New Hub' : kind === 'spoke' ? 'New Spoke' : 'New Sub-Spoke'
  const keyword = topic.toLowerCase()
  const id = typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : `node_${Math.random().toString(36).slice(2, 9)}`

  return {
    id,
    parentId,
    kind,
    topic,
    primaryKeyword: keyword,
    metrics: defaultMetrics(),
    children: [],
    brief: defaultBrief(topic, kind)
  }
}

function mutateNode(nodes, nodeId, callback) {
  for (const node of nodes) {
    if (node.id === nodeId) {
      callback(node)
      return true
    }
    if (node.children?.length) {
      if (mutateNode(node.children, nodeId, callback)) {
        return true
      }
    }
  }
  return false
}

function removeNode(nodes, nodeId) {
  const index = nodes.findIndex((item) => item.id === nodeId)
  if (index >= 0) {
    nodes.splice(index, 1)
    return true
  }
  for (const node of nodes) {
    if (node.children?.length && removeNode(node.children, nodeId)) {
      return true
    }
  }
  return false
}

export const useGraphStore = defineStore('graph', {
  state: () => ({
    nodes: []
  }),
  getters: {
    flattened(state) {
      const rows = []
      const walk = (items, depth = 0, parent = null) => {
        items.forEach((node) => {
          rows.push({ ...node, depth, parent })
          if (node.children?.length) {
            walk(node.children, depth + 1, node)
          }
        })
      }
      walk(state.nodes)
      return rows
    }
  },
  actions: {
    setNodes(nodes) {
      this.nodes = Array.isArray(nodes) ? nodes : []
    },
    updateNode(nodeId, payload) {
      mutateNode(this.nodes, nodeId, (node) => {
        Object.assign(node, payload)
      })
    },
    updateMetrics(nodeId, metrics) {
      mutateNode(this.nodes, nodeId, (node) => {
        node.metrics = { ...node.metrics, ...metrics }
      })
    },
    updateBrief(nodeId, brief) {
      mutateNode(this.nodes, nodeId, (node) => {
        node.brief = { ...node.brief, ...brief }
      })
    },
    addChild(parentId) {
      if (!parentId) {
        this.nodes.push(createLocalNode('hub'))
        return
      }
      mutateNode(this.nodes, parentId, (parent) => {
        const kind = parent.kind === 'hub' ? 'spoke' : 'subspoke'
        const child = createLocalNode(kind, parent.id)
        parent.children = parent.children || []
        parent.children.push(child)
      })
    },
    remove(nodeId) {
      removeNode(this.nodes, nodeId)
    },
    async regenerateBrief(nodeId) {
      const projectStore = useProjectStore()
      if (!projectStore.currentProjectId) return null
      const result = await api.post(`/nodes/${nodeId}/brief`, {
        projectId: projectStore.currentProjectId
      })
      this.nodes = result.nodes || []
      projectStore.links = result.links || projectStore.links
      return result
    },
    async promote(nodeId) {
      const projectStore = useProjectStore()
      if (!projectStore.currentProjectId) return null
      const result = await api.post(`/nodes/${nodeId}/promote`, {
        projectId: projectStore.currentProjectId
      })
      this.nodes = result.nodes || []
      projectStore.links = result.links || projectStore.links
      return result
    },
    async demote(nodeId) {
      const projectStore = useProjectStore()
      if (!projectStore.currentProjectId) return null
      const result = await api.post(`/nodes/${nodeId}/demote`, {
        projectId: projectStore.currentProjectId
      })
      this.nodes = result.nodes || []
      projectStore.links = result.links || projectStore.links
      return result
    },
    async merge(primaryId, secondaryId) {
      const projectStore = useProjectStore()
      if (!projectStore.currentProjectId) return null
      const result = await api.post('/nodes/merge', {
        projectId: projectStore.currentProjectId,
        primaryId,
        secondaryId
      })
      this.nodes = result.nodes || []
      projectStore.links = result.links || projectStore.links
      return result
    },
    async split(nodeId) {
      const projectStore = useProjectStore()
      if (!projectStore.currentProjectId) return null
      const result = await api.post('/nodes/split', {
        projectId: projectStore.currentProjectId,
        nodeId
      })
      this.nodes = result.nodes || []
      projectStore.links = result.links || projectStore.links
      return result
    }
  }
})
