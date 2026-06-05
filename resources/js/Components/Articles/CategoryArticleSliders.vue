<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import ArticleCard from './ArticleCard.vue'

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    allArticlesOpen: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle-all-articles'])

const CARD_STEP = 200 + 16

const scrollState = ref({})

const trackId = (categoryId) => `slider-track-${categoryId}`

const updateScrollState = (categoryId) => {
    const track = document.getElementById(trackId(categoryId))
    if (!track) return

    const maxScroll = track.scrollWidth - track.clientWidth
    scrollState.value[categoryId] = {
        canPrev: track.scrollLeft > 4,
        canNext: track.scrollLeft < maxScroll - 4,
        hasOverflow: maxScroll > 4,
    }
}

const refreshAllScrollStates = () => {
    document.querySelectorAll('[data-slider-track]').forEach((el) => {
        const id = el.dataset.categoryId
        if (id) updateScrollState(Number(id))
    })
}

const scroll = (categoryId, dir) => {
    const track = document.getElementById(trackId(categoryId))
    if (!track) return

    track.scrollTo({
        left: track.scrollLeft + dir * CARD_STEP,
        behavior: 'smooth',
    })
}

const onTrackScroll = (categoryId) => {
    updateScrollState(categoryId)
}

const toggleAllArticles = () => {
    emit('toggle-all-articles')
    if (!props.allArticlesOpen) {
        nextTick(() => {
            document.getElementById('all-articles-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        })
    }
}

let resizeObserver = null

const bindResizeObserver = () => {
    resizeObserver?.disconnect()
    resizeObserver = new ResizeObserver(() => refreshAllScrollStates())
    document.querySelectorAll('[data-slider-track]').forEach((el) => resizeObserver.observe(el))
}

onMounted(() => {
    nextTick(() => {
        refreshAllScrollStates()
        bindResizeObserver()
    })
    window.addEventListener('resize', refreshAllScrollStates)
})

watch(() => props.sliders, () => nextTick(refreshAllScrollStates), { deep: true })

onUnmounted(() => {
    resizeObserver?.disconnect()
    window.removeEventListener('resize', refreshAllScrollStates)
})
</script>

<template>
    <div v-if="sliders.length" class="category-sliders">
        <div v-for="row in sliders" :key="row.category.id" class="slider-row">
            <div class="slider-head">
                <h2 class="slider-title">{{ row.category.name }}</h2>
                <button
                    type="button"
                    class="goto-link content-link"
                    @click="$emit('goto-category', row.category.id)"
                >
                    Перейти →
                </button>
            </div>

            <div class="slider-wrap">
                <button
                    type="button"
                    class="slider-nav"
                    aria-label="Назад"
                    :disabled="!scrollState[row.category.id]?.canPrev"
                    @click="scroll(row.category.id, -1)"
                >
                    ‹
                </button>
                <div class="slider-viewport">
                    <div
                        :id="trackId(row.category.id)"
                        data-slider-track
                        :data-category-id="row.category.id"
                        class="slider-track"
                        @scroll="onTrackScroll(row.category.id)"
                    >
                        <ArticleCard
                            v-for="article in row.articles"
                            :key="`${row.category.id}-${article.id}`"
                            :article="article"
                            class="slider-card"
                        />
                    </div>
                </div>
                <button
                    type="button"
                    class="slider-nav"
                    aria-label="Вперёд"
                    :disabled="!scrollState[row.category.id]?.canNext"
                    @click="scroll(row.category.id, 1)"
                >
                    ›
                </button>
            </div>
        </div>

        <div class="all-articles-foot">
            <button
                type="button"
                class="all-articles-toggle"
                :class="{ open: allArticlesOpen }"
                @click="toggleAllArticles"
            >
                {{ allArticlesOpen ? 'Скрыть все статьи' : 'Все статьи' }}
                <span class="chevron" aria-hidden="true">{{ allArticlesOpen ? '▲' : '▼' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.category-sliders {
    --slider-card-width: 200px;
    --slider-gap: 1rem;
    --slider-visible-count: 6;
    --slider-viewport-width: calc(
        var(--slider-visible-count) * var(--slider-card-width)
        + (var(--slider-visible-count) - 1) * var(--slider-gap)
    );
    width: 100%;
    max-width: 100%;
    margin: 0 0 1.5rem;
    justify-self: stretch;
}
.slider-row {
    width: 100%;
    margin-bottom: 2rem;
}
.slider-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.75rem;
    padding: 0 0.25rem;
}
.slider-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}
.goto-link {
    white-space: nowrap;
    font-size: 0.95rem;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-family: inherit;
}
.slider-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
.slider-viewport {
    flex: 1 1 0;
    width: 100%;
    max-width: var(--slider-viewport-width);
    min-width: 0;
    overflow: hidden;
    display: flex;
    justify-content: center;
}
.slider-track {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: stretch;
    gap: var(--slider-gap);
    width: max-content;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    scroll-snap-type: x proximity;
    padding: 0.25rem 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
}
.slider-track::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}
.slider-track :deep(.article-card) {
    flex: 0 0 var(--slider-card-width);
    width: var(--slider-card-width);
    max-width: var(--slider-card-width);
    height: 380px;
    align-self: stretch;
    scroll-snap-align: start;
}
.slider-track :deep(.card-link) {
    min-height: 0;
    height: 100%;
    box-sizing: border-box;
}
.slider-nav {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    font-size: 1.25rem;
    line-height: 1;
    transition: opacity 0.2s, background 0.2s;
}
.slider-nav:disabled {
    opacity: 0.35;
    cursor: default;
}
.all-articles-foot {
    display: flex;
    justify-content: center;
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}
.all-articles-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    font-size: 1.15rem;
    font-weight: 600;
    color: #4299e1;
    cursor: pointer;
    padding: 0.35rem 0.5rem;
    font-family: inherit;
}
.all-articles-toggle:hover {
    color: #3182ce;
}
.all-articles-toggle.open {
    color: #2b6cb0;
}
.chevron {
    font-size: 0.85rem;
    line-height: 1;
}
[data-theme="dark"] .slider-nav {
    background: var(--theme_black);
    border-color: #404040;
    color: #fff;
}
[data-theme="dark"] .all-articles-foot {
    border-color: #333;
}
[data-theme="dark"] .all-articles-toggle {
    color: #63b3ed;
}
[data-theme="dark"] .all-articles-toggle:hover {
    color: #90cdf4;
}
[data-theme="dark"] .goto-link {
    color: #63b3ed;
}

@media (max-width: 768px) {
    .category-sliders {
        --slider-card-width: 160px;
        --slider-visible-count: 2;
    }

    .slider-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
    }

    .slider-title {
        font-size: 1.25rem;
    }

    .slider-nav {
        width: 32px;
        height: 32px;
        font-size: 1.1rem;
    }
}
</style>
