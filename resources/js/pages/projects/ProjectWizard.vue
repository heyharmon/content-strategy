<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import StepSeed from '@/components/Wizard/StepSeed.vue'
import StepOptions from '@/components/Wizard/StepOptions.vue'
import StepPreview from '@/components/Wizard/StepPreview.vue'
import { useProjectStore } from '@/stores/project'

const router = useRouter()
const projectStore = useProjectStore()

const stepIndex = ref(0)
const steps = ['Seed', 'Options', 'Preview']

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

const isLastStep = computed(() => stepIndex.value === steps.length - 1)

const next = async () => {
  if (isLastStep.value) {
    await generate()
    return
  }
  stepIndex.value += 1
}

const prev = () => {
  stepIndex.value = Math.max(0, stepIndex.value - 1)
}

const generate = async () => {
  if (!form.seed) {
    window.alert('Please provide a seed keyword to continue.')
    return
  }
  try {
    const payload = JSON.parse(JSON.stringify(form))
    const result = await projectStore.createProject(payload)
    router.push({ name: 'projects.workspace', params: { id: result.projectId } })
  } catch (error) {
    console.error(error)
  }
}
</script>

<template>
  <div class="mx-auto flex max-w-4xl flex-col gap-6 px-4 py-10">
    <div>
      <p class="text-sm uppercase tracking-wide text-neutral-500">Wizard Step {{ stepIndex + 1 }} of {{ steps.length }}</p>
      <h1 class="text-2xl font-semibold text-neutral-900">Generate hub & spoke model</h1>
      <p class="text-sm text-neutral-600">Follow the guided steps to define inputs and generate your topic cluster.</p>
    </div>

    <div class="rounded-lg border border-neutral-200 bg-white p-6 shadow-sm">
      <StepSeed v-if="stepIndex === 0" v-model="form" />
      <StepOptions v-else-if="stepIndex === 1" v-model="form" />
      <StepPreview v-else :seed="form" />
    </div>

    <div class="flex items-center justify-between">
      <button
        type="button"
        class="rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
        :disabled="stepIndex === 0"
        @click="prev"
      >
        Back
      </button>
      <button
        type="button"
        class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white hover:bg-neutral-800"
        :disabled="projectStore.loading"
        @click="next"
      >
        {{ isLastStep ? (projectStore.loading ? 'Generating…' : 'Generate Structure') : 'Next' }}
      </button>
    </div>
  </div>
</template>
