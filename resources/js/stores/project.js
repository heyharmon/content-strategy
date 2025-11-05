import { defineStore } from 'pinia'
import api from '@/services/api'
import { useGraphStore } from '@/stores/graph'

export const useProjectStore = defineStore('project', {
  state: () => ({
    currentProjectId: null,
    metadata: null,
    projects: [],
    links: [],
    loading: false,
    error: null
  }),
  actions: {
    clearError() {
      this.error = null
    },
    upsertProjectList(project) {
      const index = this.projects.findIndex((item) => item.id === project.id)
      if (index >= 0) {
        this.projects[index] = { ...this.projects[index], ...project }
      } else {
        this.projects.push(project)
      }
    },
    async createProject(payload) {
      this.loading = true
      this.error = null
      try {
        const result = await api.post('/projects', payload)
        this.currentProjectId = result.projectId
        this.metadata = result.metadata || null
        this.links = result.links || []

        const graph = useGraphStore()
        graph.setNodes(result.nodes || [])

        this.upsertProjectList({
          id: result.projectId,
          name: payload.seed,
          locale: payload.locale || 'en-US',
          createdAt: new Date().toISOString()
        })

        return result
      } catch (error) {
        this.error = error.message || 'Unable to create project.'
        throw error
      } finally {
        this.loading = false
      }
    },
    async fetchProject(projectId) {
      if (!projectId) return null
      this.loading = true
      this.error = null

      try {
        const result = await api.get(`/projects/${projectId}`)
        this.currentProjectId = result.projectId
        this.metadata = result.metadata || null
        this.links = result.links || []

        const graph = useGraphStore()
        graph.setNodes(result.nodes || [])

        if (result.metadata?.seed) {
          this.upsertProjectList({
            id: result.projectId,
            name: result.metadata.seed,
            locale: result.metadata.locale,
            createdAt: result.metadata.createdAt || new Date().toISOString()
          })
        }

        return result
      } catch (error) {
        this.error = error.message || 'Unable to load project.'
        throw error
      } finally {
        this.loading = false
      }
    },
    async fetchLinks(projectId) {
      if (!projectId) return []
      try {
        const result = await api.get(`/projects/${projectId}/links`)
        this.links = result.links || []
        return this.links
      } catch (error) {
        this.error = error.message || 'Unable to load link suggestions.'
        throw error
      }
    },
    async exportProject({ id, format }) {
      if (!id) return null
      const responseType = format === 'csv' ? 'text' : 'json'
      return api.get(`/projects/${id}/export`, {
        params: { format },
        responseType
      })
    }
  }
})
