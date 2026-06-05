<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    article: { type: Object, required: true },
    showAuthor: { type: Boolean, default: true },
})


const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU')
}
</script>

<template>
    <article class="article-card">
        <Link :href="route('articles.show', article.slug)" class="card-link">
            <div class="cover-wrap">
                <img
                    v-if="article.banner"
                    :src="article.banner"
                    alt="Обложка"
                    class="book-cover"
                />
                <div v-else class="book-cover book-cover--empty" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 4h10a2 2 0 0 1 2 2v14H7a2 2 0 0 1-2-2V4z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M7 4v16M17 6h2a1 1 0 0 1 1 1v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            <h2 class="card-title">{{ article.title }}</h2>

            <div v-if="showAuthor" class="card-meta">
                <span class="author">{{ article.user?.name }}</span>
                <span class="date">{{ formatDate(article.created_at) }}</span>
            </div>
            <div v-else class="card-meta card-meta--publication">
                <span v-if="article.published_at" class="date">{{ formatDate(article.published_at) }}</span>
                <span v-else class="date date--unpublished">Не опубликовано</span>
            </div>

            <div v-if="article.is_coauthor" class="card-coauthor">Со-автор</div>
            <div v-if="!article.is_published && !article.is_publishable" class="card-draft card-draft--plain">Черновик</div>
            <div v-else-if="article.is_publishable && !article.is_published" class="card-draft card-draft--publishable">Публикуется</div>
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
.book-cover {
    width: 100%;
    max-width: 130px;
    aspect-ratio: 9 / 16;
    object-fit: cover;
    border-radius: 4px;
    display: block;
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
.author {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    overflow-wrap: anywhere;
    flex: 1;
    min-width: 0;
    line-height: 1.3;
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
.book-cover--empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #edf2f7;
    color: #a0aec0;
}
.book-cover--empty svg {
    width: 42%;
    height: auto;
}
[data-theme="dark"] .book-cover--empty {
    background: #1f1f1f;
    color: #666;
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
[data-theme="dark"] .article-card { background: var(--theme_black); border-color: #333; }
[data-theme="dark"] .card-title { color: #f0f0f0; }
</style>
