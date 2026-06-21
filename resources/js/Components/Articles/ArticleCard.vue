<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatRating } from '@/lib/formatRating'
import CoauthorsMore from '@/Components/UI/CoauthorsMore.vue'
import ArticleCoverThumb from '@/Components/Articles/ArticleCoverThumb.vue'

const props = defineProps({
    article: { type: Object, required: true },
    showAuthor: { type: Boolean, default: true },
    compact: { type: Boolean, default: false },
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU')
}

const coauthors = computed(() => props.article.coauthors ?? [])
</script>

<template>
    <article class="article-card" :class="{ 'article-card--compact': compact }">
        <Link :href="route('articles.show', article.slug)" class="card-link">
            <div class="cover-wrap">
                <ArticleCoverThumb :src="article.banner" />
            </div>

            <h2 class="card-title">{{ article.title }}</h2>

            <div v-if="showAuthor" class="card-meta">
                <span class="author-line">
                    <span class="author">{{ article.user?.name }}</span>
                    <CoauthorsMore :coauthors="coauthors" />
                </span>
                <span class="date">{{ formatDate(article.created_at) }}</span>
            </div>
            <div v-else class="card-meta card-meta--publication">
                <span v-if="article.published_at" class="date">{{ formatDate(article.published_at) }}</span>
                <span v-else class="date date--unpublished">Не опубликовано</span>
            </div>

            <div v-if="article.is_coauthor" class="card-coauthor">Со-автор</div>
            <div v-if="!article.is_published && !article.is_publishable" class="card-draft card-draft--plain">Черновик</div>
            <div v-else-if="article.is_publishable && !article.is_published" class="card-draft card-draft--publishable">Публикуется</div>

            <div class="card-rating" aria-label="Рейтинг статьи">
                <span class="card-rating-value">{{ formatRating(article.average_rating || 0) }}</span>
                <span class="card-rating-star" aria-hidden="true">★</span>
            </div>
        </Link>
    </article>
</template>

<style scoped>
.article-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s;
    min-width: 0;
}
.article-card--compact {
    width: 200px;
    max-width: 200px;
    height: 380px;
    flex: none;
}
.article-card--compact .card-link {
    min-height: 0;
    height: 100%;
}
.article-card--compact .cover-wrap :deep(.article-cover-thumb) {
    width: 130px;
    max-width: 130px;
    flex-shrink: 0;
}
.article-card--compact .cover-wrap {
    justify-content: center;
}
.article-card:hover { box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
.card-link {
    display: flex;
    flex-direction: column;
    flex: 1;
    height: 100%;
    min-height: 380px;
    box-sizing: border-box;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
    position: relative;
}
.cover-wrap {
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    margin-bottom: 0.75rem;
    min-height: 0;
}
.cover-wrap :deep(.article-cover-thumb) {
    width: 100%;
    max-width: 130px;
    aspect-ratio: 9 / 16;
    border-radius: 4px;
    flex-shrink: 0;
}
.cover-wrap :deep(img.article-cover-thumb) {
    display: block;
    object-fit: cover;
}
.cover-wrap :deep(.article-cover-thumb--empty) {
    display: flex;
    align-items: center;
    justify-content: center;
}
.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    margin: 0 0 0.5rem;
    color: #2d3748;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #718096;
    margin-top: auto;
    min-height: 2.75rem;
}
.author-line {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 0.25rem;
    flex: 1;
    min-width: 0;
}
.author {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.3;
}
.author-line :deep(.coauthors-more) {
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
}
.date {
    flex-shrink: 0;
    white-space: nowrap;
}
.card-meta--publication {
    justify-content: center;
    text-align: center;
}
.date--unpublished {
    color: #a0aec0;
    font-style: italic;
}
.card-draft {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
}
.card-coauthor {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
    background: #0db7ff;
    color: #fff;
}
.card-draft--plain { background: #fed7d7; color: #c53030; }
.card-draft--publishable { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
.card-rating {
    position: absolute;
    right: 0.75rem;
    bottom: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: #4a5568;
    background: rgba(255, 255, 255, 0.92);
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
}
.card-rating-star {
    color: #f59e0b;
    font-size: 0.95rem;
    line-height: 1;
}
[data-theme="dark"] .card-rating {
    background: rgba(20, 20, 20, 0.92);
    color: #f0f0f0;
}
[data-theme="dark"] .card-rating-star {
    color: #ffffff;
}
[data-theme="dark"] .article-card { background: var(--theme_black); border-color: #333; }
[data-theme="dark"] .card-title { color: #f0f0f0; }

@media (max-width: 768px) {
    .article-card--compact {
        width: 168px;
        max-width: 168px;
        height: 320px;
    }

    .article-card--compact .cover-wrap :deep(.article-cover-thumb) {
        width: 96px;
        max-width: 96px;
    }
}
</style>
