<script setup>
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    tags: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
})

const emit = defineEmits(['preview'])

const searchInput = ref(null)
const q = ref(props.filters.q ?? '')
const selectedTags = ref(Array.isArray(props.filters.tags) ? props.filters.tags.map(String) : [])
const selectedCategories = ref(Array.isArray(props.filters.categories) ? props.filters.categories.map(String) : [])
const showFilters = ref(false)

let debounceTimer = null
let navigateTimer = null
let typingTimer = null
let isTyping = false

const inertiaOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['articles', 'filters', 'exactArticles', 'otherArticles', 'searchMeta', 'categorySliders', 'activeCategory'],
}

const buildParams = () => ({
    q: q.value.trim() || undefined,
    tags: selectedTags.value.length ? selectedTags.value : undefined,
    categories: selectedCategories.value.length ? selectedCategories.value : undefined,
    section: props.filters.section || undefined,
    category: props.filters.category || undefined,
})

const syncLiveSearch = () => {
    clearTimeout(navigateTimer)
    navigateTimer = setTimeout(() => {
        router.get(route('articles.index'), buildParams(), inertiaOptions)
    }, 400)
}

const fetchPreview = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(async () => {
        const term = q.value.trim()
        if (!term) {
            emit('preview', [])
            return
        }
        try {
            const { data } = await window.axios.get(route('articles.search-preview'), {
                params: {
                    q: term,
                    tags: selectedTags.value.length ? selectedTags.value : undefined,
                    categories: selectedCategories.value.length ? selectedCategories.value : undefined,
                },
            })
            emit('preview', data.items ?? [])
        } catch {
            emit('preview', [])
        }
    }, 300)
}

const markTyping = () => {
    isTyping = true
    clearTimeout(typingTimer)
    typingTimer = setTimeout(() => {
        isTyping = false
    }, 800)
}

const onQueryInput = () => {
    markTyping()
    fetchPreview()
    syncLiveSearch()
}

const onFiltersChange = () => {
    fetchPreview()
    syncLiveSearch()
}

const submit = () => {
    isTyping = false
    router.get(route('articles.index'), {
        ...buildParams(),
        search: q.value.trim() ? 1 : undefined,
    }, inertiaOptions)
}

const syncFromServer = (f) => {
    if (isTyping || document.activeElement === searchInput.value) {
        return
    }

    const serverQ = f.q ?? ''
    if (serverQ !== q.value) {
        q.value = serverQ
    }

    const serverTags = Array.isArray(f.tags) ? f.tags.map(String) : []
    const serverCategories = Array.isArray(f.categories) ? f.categories.map(String) : []

    if (JSON.stringify(serverTags) !== JSON.stringify(selectedTags.value)) {
        selectedTags.value = serverTags
    }
    if (JSON.stringify(serverCategories) !== JSON.stringify(selectedCategories.value)) {
        selectedCategories.value = serverCategories
    }
}

watch(() => props.filters, syncFromServer, { deep: true })
watch([selectedTags, selectedCategories], onFiltersChange, { deep: true })
</script>

<template>
    <div class="search-wrap">
        <form class="search-form" @submit.prevent="submit">
            <div class="search-input-wrap">
                <span v-show="!q" class="search-hint" aria-hidden="true">
                    Введите запрос, например название статьи или <span class="hint-accent">Автор:Имя автора</span>
                </span>
                <input
                    ref="searchInput"
                    v-model="q"
                    type="search"
                    class="search-input"
                    autocomplete="off"
                    @input="onQueryInput"
                />
            </div>
            <button type="button" class="filter-toggle" title="Фильтры" @click="showFilters = !showFilters">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round"/>
                </svg>
            </button>
            <button type="submit" class="search-submit">Найти</button>
        </form>

        <div v-if="showFilters" class="filters-panel">
            <fieldset class="tags-fieldset">
                <legend>Категории</legend>
                <template v-if="categories.length">
                    <label v-for="cat in categories" :key="cat.id" class="tag-check">
                        <input v-model="selectedCategories" type="checkbox" :value="String(cat.id)" />
                        {{ cat.name }}
                    </label>
                </template>
                <p v-else class="filter-empty">На данный момент нет категорий</p>
            </fieldset>
            <fieldset class="tags-fieldset">
                <legend>Теги</legend>
                <template v-if="tags.length">
                    <label v-for="tag in tags" :key="tag.id" class="tag-check">
                        <input v-model="selectedTags" type="checkbox" :value="String(tag.id)" />
                        {{ tag.name }}
                    </label>
                </template>
                <p v-else class="filter-empty">На данный момент нет тегов</p>
            </fieldset>
            <button type="button" class="apply-filters" @click="submit">Применить</button>
        </div>
    </div>
</template>

<style scoped>
.search-wrap { width: 100%; margin-bottom: 2rem; }
.search-form {
    display: flex;
    align-items: center;
    width: 100%;
    background: var(--search-bg, #fff);
    border: 1px solid #dfe1e5;
    border-radius: 9999px;
    padding: 0.35rem 0.5rem 0.35rem 1.25rem;
    box-shadow: 0 1px 6px rgba(32, 33, 36, 0.12);
}
.search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 0;
}
.search-hint {
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #9aa0a6;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 0.5rem;
}
.hint-accent { color: #5f6368; font-style: italic; }
.search-input {
    width: 100%;
    border: none;
    outline: none;
    box-shadow: none;
    font-size: 1rem;
    padding: 0.65rem 0.5rem;
    background: transparent;
    color: var(--page-text, #000);
}
.search-input:focus {
    outline: none;
    box-shadow: none;
}
.filter-toggle {
    border: none;
    background: transparent;
    cursor: pointer;
    color: #5f6368;
    padding: 0.5rem;
    border-radius: 50%;
    display: flex;
}
.filter-toggle:hover { background: #f1f3f4; }
.search-submit {
    border: none;
    background: var(--theme_black);
    color: white;
    padding: 0.55rem 1.25rem;
    border-radius: 9999px;
    font-weight: 600;
    cursor: pointer;
}
.filters-panel {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--search-panel-bg, #f8f9fa);
    border-radius: 12px;
    border: 1px solid #e8eaed;
    width: 100%;
}
.tags-fieldset {
    border: none;
    margin: 0 0 1rem;
    padding: 0;
}
.tags-fieldset legend {
    font-weight: 600;
    margin-bottom: 0.5rem;
    padding: 0;
}
.filter-empty {
    margin: 0;
    font-size: 0.9rem;
    color: #718096;
    font-style: italic;
}
.tag-check { display: inline-flex; gap: 0.35rem; margin: 0.25rem 1rem 0.25rem 0; font-size: 0.875rem; }
.apply-filters {
    background: #0db7ff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .search-form { --search-bg: #1c1c1c; border-color: #404040; }
[data-theme="dark"] .search-hint { color: #888; }
[data-theme="dark"] .hint-accent { color: #aaa; }
[data-theme="dark"] .filters-panel { --search-panel-bg: #1c1c1c; border-color: #404040; }
[data-theme="dark"] .filter-empty { color: #aaa; }
</style>
