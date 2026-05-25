<script setup>
import { router, usePage } from '@inertiajs/vue3'

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
</script>

<template>
    <div class="star-rating">
        <span class="label">Ваша оценка:</span>
        <div v-if="canRate" class="stars">
            <button
                v-for="n in 5"
                :key="n"
                type="button"
                class="star"
                :class="{ filled: (userRating ?? 0) >= n }"
                @click="rate(n)"
            >
                ★
            </button>
        </div>
        <span v-if="ratingsCount" class="avg">
            Средняя: {{ Number(averageRating).toFixed(1) }} ({{ ratingsCount }})
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
.label { margin-right: 0.5rem; font-weight: 600; }
.avg { margin-left: 0.75rem; color: #718096; font-size: 0.9rem; }
.hint { font-size: 0.85rem; color: #718096; margin: 0.35rem 0 0; }
</style>
