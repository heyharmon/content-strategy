<script setup>
import { computed, ref } from 'vue'
import { useVModel } from '@vueuse/core'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const form = useVModel(props, 'modelValue', emit)

const showAdvanced = ref(false)

const competitorText = computed({
  get: () => (form.value.competitors || []).join(', '),
  set: (value) => {
    const entries = value
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)
    if (!Array.isArray(form.value.competitors)) {
      form.value.competitors = []
    }
    form.value.competitors = entries
  }
})

const toggleAdvanced = () => {
  showAdvanced.value = !showAdvanced.value
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold text-neutral-900">Seed Keyword</h2>
      <p class="mt-1 text-sm text-neutral-600">
        Provide the primary keyword or topic to generate your hub and spoke model.
      </p>
    </div>

    <label class="space-y-2 block">
      <span class="text-sm font-medium text-neutral-700">Seed Keyword</span>
      <input
        v-model="form.seed"
        type="text"
        required
        placeholder="Email marketing"
        class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
      />
    </label>

    <button
      type="button"
      class="text-sm font-medium text-neutral-600 hover:text-neutral-900"
      @click="toggleAdvanced"
    >
      {{ showAdvanced ? 'Hide' : 'Show' }} advanced options
    </button>

    <div v-if="showAdvanced" class="space-y-6 rounded-lg border border-neutral-200 bg-neutral-50 p-4">
      <h3 class="text-sm font-semibold text-neutral-700">Advanced Options</h3>
      <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2">
          <span class="text-sm font-medium text-neutral-700">Locale</span>
          <input
            v-model="form.locale"
            type="text"
            placeholder="en-US"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>

        <label class="space-y-2">
          <span class="text-sm font-medium text-neutral-700">Industry (optional)</span>
          <input
            v-model="form.industry"
            type="text"
            placeholder="SaaS, eCommerce, ..."
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2">
          <span class="text-sm font-medium text-neutral-700">Competitors (comma separated)</span>
          <input
            v-model="competitorText"
            type="text"
            placeholder="Mailchimp, HubSpot"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>

        <div class="space-y-4">
          <label class="space-y-2">
            <span class="text-sm font-medium text-neutral-700">Depth</span>
            <select
              v-model.number="form.depth"
              class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
            >
              <option :value="2">2 Levels</option>
              <option :value="3">3 Levels</option>
            </select>
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-neutral-700">Max spokes per hub</span>
            <input
              v-model.number="form.maxSpokes"
              type="number"
              min="2"
              max="8"
              class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
            />
          </label>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2">
          <span class="text-sm font-medium text-neutral-700">Minimum Search Volume</span>
          <input
            v-model.number="form.constraints.minMsv"
            type="number"
            min="0"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-neutral-700">Minimum CPC ($)</span>
          <input
            v-model.number="form.constraints.minCpc"
            type="number"
            step="0.1"
            min="0"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
          />
        </label>
      </div>
    </div>
  </div>
</template>
