<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
    categories: { type: Array, default: () => [] },
    activeCategoryId: { type: [String, Number], default: null },
})

const track = ref(null)

const scroll = (dir) => {
    if (!track.value) return
    track.value.scrollBy({ left: dir * 280, behavior: 'smooth' })
}
</script>

<template>
    <div class="carousel-wrap">
        <button type="button" class="carousel-nav" aria-label="Назад" @click="scroll(-1)">‹</button>
        <div ref="track" class="carousel-track">
            <div v-for="cat in categories" :key="cat.id" class="category-card">
                <div class="category-card-head">
                    <h3>{{ cat.name }}</h3>
                    <Link
                        :href="route('articles.index', { category: cat.id })"
                        class="content-link more-link"
                    >
                        Перейти →
                    </Link>
                </div>
                <p v-if="cat.description" class="category-desc">{{ cat.description }}</p>
            </div>
        </div>
        <button type="button" class="carousel-nav" aria-label="Вперёд" @click="scroll(1)">›</button>
    </div>
</template>

<style scoped>
.carousel-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
}
.carousel-track {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    flex: 1;
    padding: 0.25rem 0;
    scrollbar-width: thin;
}
.carousel-nav {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    font-size: 1.25rem;
}
.category-card {
    flex: 0 0 260px;
    scroll-snap-align: start;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
}
.category-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}
.category-card h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1a202c;
}
.more-link {
    white-space: nowrap;
    font-size: 0.9rem;
    font-weight: 600;
}
.category-desc {
    margin: 0.5rem 0 0;
    font-size: 0.85rem;
    color: #718096;
}
[data-theme="dark"] .category-card {
    background: #111;
    border-color: #333;
}
[data-theme="dark"] .category-card h3 { color: #fff; }
[data-theme="dark"] .carousel-nav { background: #111; border-color: #333; color: #fff; }
</style>
