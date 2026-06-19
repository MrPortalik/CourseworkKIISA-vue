<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatRating } from '@/lib/formatRating'

const props = defineProps({
    articleSlug: { type: String, required: true },
    userRating: { type: Number, default: null },
    averageRating: { type: Number, default: 0 },
    ratingsCount: { type: Number, default: 0 },
    canRate: { type: Boolean, default: true },
    previousArticleSlug: { type: String, default: null },
    nextArticleSlug: { type: String, default: null },
    randomArticleSlug: { type: String, default: null },
})

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)

const rate = (stars) => {
    if (!isLoggedIn.value) {
        router.visit(route('login'))
        return
    }
    if (!props.canRate) return

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

const showInteractiveStars = computed(() => isLoggedIn.value && props.canRate)
</script>

<template>
    <div id="article-rating" class="star-rating">
        <div class="star-rating__row">
            <div class="star-rating__left">
                <span class="label">{{ showInteractiveStars ? 'Ваша оценка:' : 'Оценка:' }}</span>
                <div class="stars">
                    <button
                        v-for="n in 5"
                        :key="n"
                        type="button"
                        class="star"
                        :class="{ filled: displayRating >= n }"
                        @click="rate(n)"
                    >
                        ★
                    </button>
                </div>
                <span v-if="ratingsCount" class="avg">
                    Средняя: {{ formatRating(averageRating) }} ({{ ratingsCount }})
                </span>
            </div>

            <div v-if="previousArticleSlug || nextArticleSlug || randomArticleSlug" class="star-rating__right">
                <Link
                    v-if="previousArticleSlug"
                    :href="route('articles.show', previousArticleSlug)"
                    class="article-nav-link content-link"
                >
                    ← Открыть прошлую статью
                </Link>
                <Link
                    v-if="nextArticleSlug"
                    :href="route('articles.show', nextArticleSlug)"
                    class="article-nav-link content-link"
                >
                    Открыть следующую статью →
                </Link>
                <Link
                    v-if="randomArticleSlug"
                    :href="route('articles.show', randomArticleSlug)"
                    class="article-nav-link content-link"
                >
                    Открыть случайную статью →
                </Link>
            </div>
        </div>

        <p v-if="!isLoggedIn" class="hint">Войдите, чтобы поставить оценку</p>
        <p v-else-if="!canRate" class="hint">Автор не может оценивать свою статью</p>
    </div>
</template>

<style scoped>
.star-rating {
    margin: 1rem 0;
}
.star-rating__row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem 1.5rem;
}
.star-rating__left {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem;
    flex: 1 1 280px;
}
.star-rating__right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;
    flex: 0 1 auto;
}
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
.label { font-weight: 600; }
.avg {
    color: #718096;
    font-size: 0.9rem;
}
.article-nav-link {
    white-space: nowrap;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
}
.article-nav-link:hover {
    text-decoration: underline;
}
.hint {
    font-size: 0.85rem;
    color: #718096;
    margin: 0.35rem 0 0;
    width: 100%;
}
[data-theme="dark"] .avg,
[data-theme="dark"] .hint {
    color: #aaa;
}

@media (max-width: 768px) {
    .star-rating__row {
        flex-direction: column;
    }
    .star-rating__right {
        align-items: flex-start;
        width: 100%;
    }
}
</style>
