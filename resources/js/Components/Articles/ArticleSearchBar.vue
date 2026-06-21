<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'

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
const tagMatchMode = ref(props.filters.tags_match === 'any' ? 'any' : 'all')
const showFilters = ref(false)

let debounceTimer = null
let navigateTimer = null
let typingTimer = null
let isTyping = false

const inertiaOptions = {
    preserveScroll: true,
    replace: true,
    only: ['articles', 'filters', 'exactArticles', 'otherArticles', 'searchMeta', 'categorySliders', 'activeCategory'],
}

const buildParams = () => ({
    q: q.value.trim() || undefined,
    tags: selectedTags.value.length ? selectedTags.value : undefined,
    tags_match: selectedTags.value.length ? tagMatchMode.value : undefined,
    categories: selectedCategories.value.length ? selectedCategories.value : undefined,
    section: props.filters.section || undefined,
    category: props.filters.category || undefined,
    search: (q.value.trim() || selectedTags.value.length) ? 1 : undefined,
})

const navigateWithFilters = () => {
    router.get(route('articles.index'), buildParams(), {
        ...inertiaOptions,
        preserveState: true,
    })
}

const syncLiveSearch = () => {
    clearTimeout(navigateTimer)
    navigateTimer = setTimeout(navigateWithFilters, 400)
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
                    tags_match: selectedTags.value.length ? tagMatchMode.value : undefined,
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
    clearTimeout(navigateTimer)
    navigateTimer = setTimeout(() => {
        router.get(route('articles.index'), buildParams(), {
            ...inertiaOptions,
            preserveState: true,
        })
    }, 400)
}

const onFiltersChange = () => {
    fetchPreview()
    syncLiveSearch()
}

const submit = () => {
    isTyping = false
    navigateWithFilters()
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

    const serverTagMatch = f.tags_match === 'any' ? 'any' : 'all'
    if (serverTagMatch !== tagMatchMode.value) {
        tagMatchMode.value = serverTagMatch
    }
}

watch(() => props.filters, syncFromServer, { deep: true })
watch([selectedTags, selectedCategories, tagMatchMode], onFiltersChange, { deep: true })
</script>

<template>
    <div class="search-wrap">
        <form class="search-form" @submit.prevent="submit">
            <div class="search-input-wrap">
                <span v-show="!q" class="search-hint" aria-hidden="true">
                    Введите запрос, например название статьи или&nbsp;<span class="hint-accent">Автор:Имя автора</span>
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
                        <CatCheckbox v-model="selectedCategories" :value="String(cat.id)" />
                        {{ cat.name }}
                    </label>
                </template>
                <p v-else class="filter-empty">На данный момент нет категорий</p>
            </fieldset>
            <fieldset class="tags-fieldset">
                <legend>Теги</legend>
                <div v-if="tags.length" class="tag-match-toggle">
                    <label class="tag-match-option">
                        <input v-model="tagMatchMode" type="radio" value="all" />
                        Наличие всех тегов
                    </label>
                    <label class="tag-match-option">
                        <input v-model="tagMatchMode" type="radio" value="any" />
                        Один из включённых тегов
                    </label>
                </div>
                <template v-if="tags.length">
                    <label v-for="tag in tags" :key="tag.id" class="tag-check tag-check--with-tip">
                        <CatCheckbox v-model="selectedTags" :value="String(tag.id)" />
                        <span class="tag-check-label">{{ tag.name }}</span>
                        <span v-if="tag.description" class="tag-filter-tooltip">{{ tag.description }}</span>
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
    overflow: hidden;
}
.search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 0;
    border-radius: 9999px 0 0 9999px;
    background: var(--search-bg, #fff);
    overflow: hidden;
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
    padding: 0 0.5rem 0 2rem;
    box-sizing: border-box;
}
.hint-accent { color: #5f6368; font-style: italic; }
.search-input {
    width: 100%;
    border: none;
    outline: none;
    box-shadow: none;
    font-size: 1rem;
    padding: 0.65rem 0.5rem 0.65rem 2rem;
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
.tag-check {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0.25rem 1rem 0.25rem 0;
    font-size: 0.875rem;
}
.tag-check--with-tip {
    position: relative;
}
.tag-check-label { cursor: pointer; }
.tag-filter-tooltip {
    position: absolute;
    left: 0;
    bottom: calc(100% + 6px);
    z-index: 20;
    min-width: 140px;
    max-width: 260px;
    padding: 0.45rem 0.65rem;
    border-radius: 6px;
    background: #1a202c;
    color: #f7fafc;
    font-size: 0.78rem;
    line-height: 1.35;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.15s, visibility 0.15s;
}
.tag-check--with-tip:hover .tag-filter-tooltip {
    opacity: 1;
    visibility: visible;
}
[data-theme="dark"] .tag-filter-tooltip {
    background: #f0f0f0;
    color: #0a0a0a;
}
.apply-filters {
    background: #ffffff;
    color: #151515;
    border: 2px solid #151515;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
}
.apply-filters:hover {
    background: #f7fafc;
}
.tag-match-toggle {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    margin-bottom: 0.65rem;
}
.tag-match-option {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.875rem;
    cursor: pointer;
}
[data-theme="dark"] .apply-filters {
    background: #ffffff;
    color: #151515;
    border-color: #151515;
}
[data-theme="dark"] .apply-filters:hover {
    background: #f0f0f0;
}
[data-theme="dark"] .search-form { --search-bg: #1c1c1c; border-color: #404040; }
[data-theme="dark"] .search-hint { color: #888; }
[data-theme="dark"] .hint-accent { color: #aaa; }
[data-theme="dark"] .filters-panel { --search-panel-bg: #1c1c1c; border-color: #404040; }
[data-theme="dark"] .filter-empty { color: #aaa; }

@media (max-width: 768px) {
    .search-form {
        flex-wrap: wrap;
        border-radius: 16px;
        padding: 0.5rem 0.75rem 0.5rem 0.75rem;
    }

    .search-input-wrap {
        flex: 1 1 100%;
        order: 1;
        border-radius: 12px 0 0 12px;
    }

    .filter-toggle {
        order: 2;
    }

    .search-submit {
        order: 3;
        margin-left: auto;
        padding: 0.55rem 1rem;
        font-size: 0.9rem;
    }

    .search-hint {
        font-size: 0.82rem;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        padding-left: 1.35rem;
    }

    .search-input {
        padding-left: 1.35rem;
    }
}
</style>
