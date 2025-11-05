<script setup>
import { computed } from 'vue'
import { useVModel } from '@vueuse/core'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const form = useVModel(props, 'modelValue', emit)

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
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold text-neutral-900">Seed Keyword</h2>
      <p class="mt-1 text-sm text-neutral-600">
        Provide the primary keyword or topic to generate your hub and spoke model.
      </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Seed Keyword</span>
        <input
          v-model="form.seed"
          type="text"
          required
          placeholder="Email marketing"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>

      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Locale</span>
        <input
          v-model="form.locale"
          type="text"
          placeholder="en-US"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Industry (optional)</span>
        <input
          v-model="form.industry"
          type="text"
          placeholder="SaaS, eCommerce, ..."
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>

      <label class="space-y-2">
        <span class="text-sm font-medium text-neutral-700">Competitors (comma separated)</span>
        <input
          v-model="competitorText"
          type="text"
          placeholder="Mailchimp, HubSpot"
          class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
        />
      </label>
    </div>
  </div>
</template>
