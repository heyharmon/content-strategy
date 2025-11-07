<script setup>
import { reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import StepSeed from '@/components/Wizard/StepSeed.vue'
import { useProjectStore } from '@/stores/project'

const router = useRouter()
const projectStore = useProjectStore()

const form = reactive({
  seed: '',
  locale: 'en-US',
  industry: '',
  competitors: [],
  depth: 3,
  maxSpokes: 5,
  constraints: {
    minMsv: 0,
    minCpc: 0
  }
})

const errorMessage = ref('')

const generate = async () => {
  if (!form.seed) {
    window.alert('Please provide a seed keyword to continue.')
    return
  }
  errorMessage.value = ''
  try {
    const payload = JSON.parse(JSON.stringify(form))
    const result = await projectStore.createProject(payload)
    router.push({ name: 'projects.workspace', params: { id: result.projectId } })
  } catch (error) {
    console.error(error)
    errorMessage.value = projectStore.error || 'Unable to generate project.'
  }
}

watch(
  () => projectStore.error,
  (value) => {
    if (value) {
      errorMessage.value = value
    } else {
      errorMessage.value = ''
    }
  }
)
</script>

<template>
  <div class="mx-auto flex max-w-4xl flex-col gap-6 px-4 py-10">
    <div>
      <h1 class="text-2xl font-semibold text-neutral-900">Generate hub & spoke model</h1>
      <p class="text-sm text-neutral-600">Enter a seed keyword to jump straight into your topic workspace.</p>
    </div>

    <form class="space-y-6" @submit.prevent="generate">
      <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow-sm">
        <StepSeed v-model="form" />
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end">
        <button
          type="submit"
          class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white hover:bg-neutral-800 disabled:opacity-60"
          :disabled="projectStore.loading"
        >
          {{ projectStore.loading ? 'Generating…' : 'Generate Structure' }}
        </button>
      </div>
    </form>
  </div>
</template>
