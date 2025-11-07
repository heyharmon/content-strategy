import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    drawerOpen: false,
    activeNodeId: null,
    activeTab: 'topics',
    expanded: {},
    filters: {
      viability: 'all',
      kind: 'all',
      minGeo: 0
    },
    mergeSourceId: null
  }),
  actions: {
    toggleDrawer(open, nodeId = null) {
      if (typeof open === 'boolean') {
        this.drawerOpen = open
      } else {
        this.drawerOpen = !this.drawerOpen
      }
      if (nodeId !== undefined) {
        this.activeNodeId = nodeId
      }
      if (!this.drawerOpen) {
        this.activeNodeId = null
      }
    },
    setActiveNode(nodeId) {
      this.activeNodeId = nodeId
    },
    toggleRow(nodeId) {
      this.expanded[nodeId] = !this.expanded[nodeId]
    },
    setExpanded(nodeId, value) {
      this.expanded[nodeId] = value
    },
    setTab(tab) {
      this.activeTab = tab
    },
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },
    resetFilters() {
      this.filters = {
        viability: 'all',
        kind: 'all',
        minGeo: 0
      }
    },
    setMergeSource(nodeId) {
      this.mergeSourceId = nodeId
    }
  }
})
