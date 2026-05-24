<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import ArticleCard from './ArticleCard.vue'

defineProps({
    sliders: { type: Array, default: () => [] },
})

const tracks = ref({})

const setTrack = (id, el) => {
    if (el) tracks.value[id] = el
}

const scroll = (id, dir) => {
    const track = tracks.value[id]
    if (!track) return
    track.scrollBy({ left: dir * 240, behavior: 'smooth' })
}

const isSparse = (articles) => (articles?.length ?? 0) <= 4
</script>

<template>
    <section v-if="sliders.length" class="category-sliders">
        <div v-for="row in sliders" :key="row.category.id" class="slider-row">
            <div class="slider-head">
                <h2 class="slider-title">{{ row.category.name }}</h2>
                <Link
                    :href="route('articles.index', { category: row.category.id })"
                    class="content-link goto-link"
                >
                    Перейти →
                </Link>
            </div>

            <div class="slider-wrap">
                <button
                    type="button"
                    class="slider-nav"
                    aria-label="Назад"
                    @click="scroll(row.category.id, -1)"
                >
                    ‹
                </button>
                <div
                    :ref="(el) => setTrack(row.category.id, el)"
                    class="slider-track"
                    :class="{ 'slider-track--centered': isSparse(row.articles) }"
                >
                    <ArticleCard
                        v-for="article in row.articles"
                        :key="article.id"
                        :article="article"
                        class="slider-card"
                    />
                </div>
                <button
                    type="button"
                    class="slider-nav"
                    aria-label="Вперёд"
                    @click="scroll(row.category.id, 1)"
                >
                    ›
                </button>
            </div>
        </div>

        <div class="all-articles-foot">
            <Link :href="route('articles.index')" class="content-link all-articles-link">
                Все статьи →
            </Link>
        </div>
    </section>
</template>

<style scoped>
.category-sliders {
    width: 100%;
    margin-bottom: 2.5rem;
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
}
.slider-wrap {
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
    width: 100%;
}
.slider-track {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    flex: 1;
    min-width: 0;
    padding: 0.25rem 0;
    scrollbar-width: thin;
    justify-content: flex-start;
}
.slider-track--centered {
    justify-content: center;
    overflow-x: visible;
}
.slider-track :deep(.article-card) {
    flex: 0 0 200px;
    scroll-snap-align: start;
    max-width: 200px;
}
.slider-nav {
    flex-shrink: 0;
    align-self: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    font-size: 1.25rem;
}
.all-articles-foot {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.5rem;
    border-top: 1px solid #e2e8f0;
}
.all-articles-link {
    font-size: 1.1rem;
    font-weight: 600;
}
[data-theme="dark"] .slider-nav {
    background: var(--theme_black);
    border-color: #404040;
    color: #fff;
}
[data-theme="dark"] .all-articles-foot {
    border-color: #333;
}
</style>
