<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/project'

const router = useRouter()
const projectStore = useProjectStore()

const projects = computed(() => projectStore.projects)

const goToWizard = () => {
  router.push({ name: 'projects.wizard' })
}

const openProject = (project) => {
  router.push({ name: 'projects.workspace', params: { id: project.id } })
}
</script>

<template>
  <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-neutral-900">Content Strategy Projects</h1>
        <p class="text-sm text-neutral-600">Generate, revisit, and manage your hub and spoke content plans.</p>
      </div>
      <button
        type="button"
        class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white hover:bg-neutral-800"
        @click="goToWizard"
      >
        + New Project
      </button>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div v-for="project in projects" :key="project.id" class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-neutral-900">{{ project.name }}</h2>
            <p class="text-sm text-neutral-500">Locale: {{ project.locale }} · Created {{ new Date(project.createdAt).toLocaleString() }}</p>
          </div>
          <button
            type="button"
            class="rounded-md border border-neutral-200 px-3 py-1 text-sm text-neutral-600 hover:bg-neutral-100"
            @click="openProject(project)"
          >
            Open
          </button>
        </div>
      </div>
      <div
        v-if="!projects.length"
        class="col-span-full rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-10 text-center text-sm text-neutral-500"
      >
        No projects yet. Generate your first hub & spoke map to get started.
      </div>
    </div>
  </div>
</template>
