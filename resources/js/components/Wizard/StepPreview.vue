<script setup>
const props = defineProps({
  seed: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold text-neutral-900">Review Configuration</h2>
      <p class="mt-1 text-sm text-neutral-600">
        Double check your selections before generating the topic structure.
      </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-neutral-700">Seed Details</h3>
        <dl class="mt-3 space-y-2 text-sm text-neutral-600">
          <div class="flex justify-between">
            <dt>Keyword</dt>
            <dd class="font-medium text-neutral-900">{{ seed.seed }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Locale</dt>
            <dd class="font-medium text-neutral-900">{{ seed.locale }}</dd>
          </div>
          <div v-if="seed.industry" class="flex justify-between">
            <dt>Industry</dt>
            <dd class="font-medium text-neutral-900">{{ seed.industry }}</dd>
          </div>
          <div v-if="seed.competitors?.length" class="space-y-1">
            <dt>Competitors</dt>
            <dd class="text-neutral-900">
              <ul class="list-inside list-disc space-y-1">
                <li v-for="item in seed.competitors" :key="item">{{ item }}</li>
              </ul>
            </dd>
          </div>
        </dl>
      </div>

      <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-neutral-700">Metric Constraints</h3>
        <dl class="mt-3 space-y-2 text-sm text-neutral-600">
          <div class="flex justify-between">
            <dt>Depth</dt>
            <dd class="font-medium text-neutral-900">{{ seed.depth }} levels</dd>
          </div>
          <div class="flex justify-between">
            <dt>Max spokes per hub</dt>
            <dd class="font-medium text-neutral-900">{{ seed.maxSpokes }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Minimum MSV</dt>
            <dd class="font-medium text-neutral-900">{{ seed.constraints?.minMsv || 0 }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Minimum CPC</dt>
            <dd class="font-medium text-neutral-900">${{ seed.constraints?.minCpc || 0 }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</template>
