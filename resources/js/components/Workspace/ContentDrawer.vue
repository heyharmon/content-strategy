<script setup>
import { computed, reactive, watch } from 'vue'
import { useGraphStore } from '@/stores/graph'
import { useUiStore } from '@/stores/ui'

const graphStore = useGraphStore()
const uiStore = useUiStore()

const activeNode = computed(() =>
  graphStore.flattened.find((item) => item.id === uiStore.activeNodeId)
)

const localBrief = reactive({
  title: '',
  searchIntent: 'Informational',
  summary: '',
  outline: [],
  faqs: [],
  entities: [],
  geoPrompts: [],
  schemaHints: [],
  wordCountRange: [1200, 1800]
})

watch(
  () => activeNode.value,
  (node) => {
    if (!node?.brief) return
    Object.assign(localBrief, {
      title: node.brief.title,
      searchIntent: node.brief.searchIntent,
      summary: node.brief.summary,
      outline: [...(node.brief.outline || [])],
      faqs: [...(node.brief.faqs || [])],
      entities: [...(node.brief.entities || [])],
      geoPrompts: [...(node.brief.geoPrompts || [])],
      schemaHints: [...(node.brief.schemaHints || [])],
      wordCountRange: [...(node.brief.wordCountRange || [1200, 1800])]
    })
  },
  { immediate: true }
)

const persistBrief = () => {
  if (!activeNode.value) return
  graphStore.updateBrief(activeNode.value.id, { ...localBrief })
}

const addItem = (field, placeholder) => {
  if (!localBrief[field]) localBrief[field] = []
  localBrief[field].push(placeholder)
  persistBrief()
}

const removeItem = (field, index) => {
  localBrief[field].splice(index, 1)
  persistBrief()
}

const updateWordCount = (index, value) => {
  const range = [...localBrief.wordCountRange]
  range[index] = Number(value)
  localBrief.wordCountRange = range
  persistBrief()
}

const regenerate = () => {
  if (!activeNode.value) return
  graphStore.regenerateBrief(activeNode.value.id)
}

const copyToClipboard = async () => {
  if (!activeNode.value) return
  const text = `Title: ${localBrief.title}\nIntent: ${localBrief.searchIntent}\nSummary: ${localBrief.summary}`
  try {
    await navigator.clipboard.writeText(text)
    window.alert('Brief copied to clipboard')
  } catch (error) {
    window.alert('Unable to copy brief. Please copy manually.')
  }
}

const schemaOptions = ['Article', 'FAQPage', 'HowTo', 'Product']
</script>

<template>
  <transition name="slide">
    <aside
      v-if="uiStore.drawerOpen && activeNode"
      class="fixed inset-y-0 right-0 z-30 w-full max-w-md overflow-y-auto border-l border-neutral-200 bg-white p-6 shadow-xl"
    >
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs uppercase tracking-wide text-neutral-500">{{ activeNode.kind }}</p>
          <h2 class="text-xl font-semibold text-neutral-900">{{ activeNode.topic }}</h2>
        </div>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-3 py-1 text-sm text-neutral-600 hover:bg-neutral-100"
          @click="uiStore.toggleDrawer(false)"
        >
          Close
        </button>
      </div>

      <div class="mt-6 space-y-6">
        <section class="space-y-2">
          <label class="text-sm font-medium text-neutral-700">Suggested Title</label>
          <input
            v-model="localBrief.title"
            type="text"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
            @blur="persistBrief"
          />
        </section>

        <section class="space-y-2">
          <label class="text-sm font-medium text-neutral-700">Search Intent</label>
          <select
            v-model="localBrief.searchIntent"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
            @change="persistBrief"
          >
            <option>Informational</option>
            <option>Commercial</option>
            <option>Navigational</option>
            <option>Transactional</option>
          </select>
        </section>

        <section class="space-y-2">
          <label class="text-sm font-medium text-neutral-700">Summary</label>
          <textarea
            v-model="localBrief.summary"
            rows="4"
            class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none"
            @blur="persistBrief"
          ></textarea>
        </section>

        <section class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-neutral-700">Outline</h3>
            <button
              type="button"
              class="text-sm text-neutral-600 hover:text-neutral-900"
              @click="addItem('outline', 'New section')"
            >
              + Add
            </button>
          </div>
          <ul class="space-y-2">
            <li v-for="(item, index) in localBrief.outline" :key="index" class="flex items-center gap-2">
              <span class="text-xs text-neutral-400">{{ index + 1 }}.</span>
              <input
                v-model="localBrief.outline[index]"
                type="text"
                class="flex-1 rounded-md border border-neutral-300 px-2 py-1 text-sm focus:border-neutral-900 focus:outline-none"
                @blur="persistBrief"
              />
              <button
                type="button"
                class="text-xs text-rose-500 hover:text-rose-600"
                @click="removeItem('outline', index)"
              >
                Remove
              </button>
            </li>
          </ul>
        </section>

        <section class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-neutral-700">FAQs</h3>
            <button type="button" class="text-sm text-neutral-600 hover:text-neutral-900" @click="addItem('faqs', 'New FAQ')">
              + Add
            </button>
          </div>
          <ul class="space-y-2">
            <li v-for="(item, index) in localBrief.faqs" :key="index" class="flex items-center gap-2">
              <input
                v-model="localBrief.faqs[index]"
                type="text"
                class="flex-1 rounded-md border border-neutral-300 px-2 py-1 text-sm focus:border-neutral-900 focus:outline-none"
                @blur="persistBrief"
              />
              <button type="button" class="text-xs text-rose-500" @click="removeItem('faqs', index)">Remove</button>
            </li>
          </ul>
        </section>

        <section class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-neutral-700">Entities</h3>
            <button type="button" class="text-sm text-neutral-600 hover:text-neutral-900" @click="addItem('entities', 'New entity')">
              + Add
            </button>
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="(item, index) in localBrief.entities"
              :key="item + index"
              class="inline-flex items-center gap-2 rounded-full bg-neutral-100 px-3 py-1 text-sm text-neutral-700"
            >
              {{ item }}
              <button type="button" class="text-xs text-neutral-500" @click="removeItem('entities', index)">×</button>
            </span>
          </div>
        </section>

        <section class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-neutral-700">GEO Prompts</h3>
            <button type="button" class="text-sm text-neutral-600 hover:text-neutral-900" @click="addItem('geoPrompts', 'Localize this topic')">
              + Add
            </button>
          </div>
          <ul class="space-y-2">
            <li v-for="(item, index) in localBrief.geoPrompts" :key="index" class="flex items-center gap-2">
              <input
                v-model="localBrief.geoPrompts[index]"
                type="text"
                class="flex-1 rounded-md border border-neutral-300 px-2 py-1 text-sm focus:border-neutral-900 focus:outline-none"
                @blur="persistBrief"
              />
              <button type="button" class="text-xs text-rose-500" @click="removeItem('geoPrompts', index)">Remove</button>
            </li>
          </ul>
        </section>

        <section class="space-y-3">
          <h3 class="text-sm font-semibold text-neutral-700">Schema Suggestions</h3>
          <div class="flex flex-wrap gap-3 text-sm text-neutral-600">
            <label v-for="schema in schemaOptions" :key="schema" class="inline-flex items-center gap-2">
              <input
                type="checkbox"
                :value="schema"
                v-model="localBrief.schemaHints"
                @change="persistBrief"
              />
              <span>{{ schema }}</span>
            </label>
          </div>
        </section>

        <section class="space-y-3">
          <h3 class="text-sm font-semibold text-neutral-700">Recommended Word Count</h3>
          <div class="flex gap-3 text-sm text-neutral-600">
            <label class="flex flex-1 flex-col gap-1">
              <span>Minimum</span>
              <input
                :value="localBrief.wordCountRange[0]"
                type="number"
                class="rounded-md border border-neutral-300 px-3 py-2 focus:border-neutral-900 focus:outline-none"
                @change="updateWordCount(0, $event.target.value)"
              />
            </label>
            <label class="flex flex-1 flex-col gap-1">
              <span>Maximum</span>
              <input
                :value="localBrief.wordCountRange[1]"
                type="number"
                class="rounded-md border border-neutral-300 px-3 py-2 focus:border-neutral-900 focus:outline-none"
                @change="updateWordCount(1, $event.target.value)"
              />
            </label>
          </div>
        </section>
      </div>

      <div class="mt-8 flex flex-wrap gap-3">
        <button
          type="button"
          class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white hover:bg-neutral-800"
          @click="regenerate"
        >
          Regenerate Brief
        </button>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="copyToClipboard"
        >
          Copy to Clipboard
        </button>
        <button
          type="button"
          class="rounded-md border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100"
          @click="uiStore.toggleDrawer(false)"
        >
          Close Drawer
        </button>
      </div>
    </aside>
  </transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
</style>
