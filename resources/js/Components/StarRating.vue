<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatRating } from '@/lib/formatRating'

const props = defineProps({
    articleSlug: { type: String, required: true },
    userRating: { type: Number, default: null },
    averageRating: { type: Number, default: 0 },
    ratingsCount: { type: Number, default: 0 },
    canRate: { type: Boolean, default: true },
})

const page = usePage()

const rate = (stars) => {
    if (!page.props.auth?.user || !props.canRate) return
    const opts = { preserveScroll: true }
    if (props.userRating === stars) {
        router.delete(route('articles.unrate', props.articleSlug), opts)
        return
    }
    router.post(route('articles.rate', props.articleSlug), { rating: stars }, opts)
}

const displayRating = computed(() => {
    if (props.userRating != null) return props.userRating
    if (props.ratingsCount > 0) return Math.round(Number(props.averageRating))
    return 0
})

const showInteractiveStars = computed(() => page.props.auth?.user && props.canRate)
</script>

<template>
    <div id="article-rating" class="star-rating">
        <span class="label">{{ showInteractiveStars ? 'Ваша оценка:' : 'Оценка:' }}</span>
        <div class="stars">
            <button
                v-for="n in 5"
                :key="n"
                type="button"
                class="star"
                :class="{ filled: displayRating >= n }"
                :disabled="!showInteractiveStars"
                @click="rate(n)"
            >
                ★
            </button>
        </div>
        <span v-if="ratingsCount" class="avg">
            Средняя: {{ formatRating(averageRating) }} ({{ ratingsCount }})
        </span>
        <p v-if="!$page.props.auth?.user" class="hint">Войдите, чтобы поставить оценку</p>
        <p v-else-if="!canRate" class="hint">Автор не может оценивать свою статью</p>
    </div>
</template>

<style scoped>
.star-rating { margin: 1rem 0; }
.stars { display: inline-flex; gap: 0.15rem; }
.star {
    background: none;
    border: none;
    font-size: 1.75rem;
    color: #cbd5e0;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.star.filled { color: #f6ad55; }
.star:disabled {
    cursor: default;
}
.label { margin-right: 0.5rem; font-weight: 600; }
.avg { margin-left: 0.75rem; color: #718096; font-size: 0.9rem; }
.hint { font-size: 0.85rem; color: #718096; margin: 0.35rem 0 0; }
</style>
