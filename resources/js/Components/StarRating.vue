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
                    class="article-nav-link article-nav-link--prev content-link"
                >
                    <span class="article-nav-arrow" aria-hidden="true">←</span>
                    <span class="article-nav-text">Открыть прошлую статью</span>
                </Link>
                <Link
                    v-if="nextArticleSlug"
                    :href="route('articles.show', nextArticleSlug)"
                    class="article-nav-link article-nav-link--next content-link"
                >
                    <span class="article-nav-text">Открыть следующую статью</span>
                    <span class="article-nav-arrow" aria-hidden="true">→</span>
                </Link>
                <Link
                    v-if="randomArticleSlug"
                    :href="route('articles.show', randomArticleSlug)"
                    class="article-nav-link article-nav-link--random content-link"
                >
                    <span class="article-nav-text">Открыть случайную статью</span>
                    <span class="article-nav-arrow" aria-hidden="true">→</span>
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
    gap: 0.2rem;
    flex: 0 1 auto;
    --nav-link-width: 18.5rem;
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
    display: grid;
    align-items: center;
    width: var(--nav-link-width);
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.3;
    text-decoration: none;
}
.article-nav-link:hover {
    text-decoration: none;
}
.article-nav-link:hover .article-nav-text {
    text-decoration: underline;
}
.article-nav-link--prev {
    grid-template-columns: 1.1rem 1fr;
    column-gap: 0.4rem;
}
.article-nav-link--prev .article-nav-arrow {
    text-align: left;
}
.article-nav-link--prev .article-nav-text {
    text-align: right;
}
.article-nav-link--next,
.article-nav-link--random {
    grid-template-columns: 1fr 1.1rem;
    column-gap: 0.4rem;
}
.article-nav-link--next .article-nav-text,
.article-nav-link--random .article-nav-text {
    text-align: left;
}
.article-nav-link--next .article-nav-arrow,
.article-nav-link--random .article-nav-arrow {
    text-align: right;
}
.article-nav-arrow {
    flex-shrink: 0;
    font-size: 1em;
    line-height: 1;
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
        align-items: stretch;
        width: 100%;
        --nav-link-width: 100%;
    }
}
</style>
