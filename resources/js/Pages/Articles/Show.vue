<script setup>
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import StarRating from '@/Components/StarRating.vue'
import CommentThread from '@/Components/CommentThread.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    article: Object,
    comments: Array,
    userRating: Number,
    userCommentVotes: Object,
    canRate: { type: Boolean, default: true },
})

const page = usePage()

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''

const canEdit = computed(() => {
    const u = page.props.auth?.user
    return u && (u.id === props.article.user_id || u.role === 'admin')
})

const deleteArticle = () => {
    if (confirm('Удалить статью?')) router.delete(route('articles.destroy', props.article.slug))
}
</script>

<template>
    <Head><title>{{ article.title }}</title></Head>

    <PageWithSidebar>
        <article class="article-show">
            <img v-if="article.hero_banner" :src="article.hero_banner" alt="" class="hero-banner" />

            <header class="article-header">
                <h1>{{ article.title }}</h1>
                <div class="byline">
                    <Link :href="route('authors.show', article.user_id)" class="author-link">
                        {{ article.user?.name }}
                    </Link>
                    <span class="dot">·</span>
                    <time class="article-date">{{ formatDate(article.created_at) }}</time>
                </div>
            </header>

            <div v-if="canEdit" class="article-actions">
                <Link :href="route('articles.edit', article.slug)" class="edit-btn">Редактировать</Link>
                <button type="button" class="delete-btn" @click="deleteArticle">Удалить</button>
            </div>

            <img v-if="article.banner" :src="article.banner" alt="" class="book-cover" />

            <div class="article-content" v-html="article.content" />

            <hr class="article-divider" />

            <StarRating
                :article-slug="article.slug"
                :user-rating="userRating"
                :average-rating="article.average_rating"
                :ratings-count="article.ratings_count"
                :can-rate="canRate"
            />

            <Link :href="route('articles.index')" class="back-link">← Назад к статьям</Link>

            <CommentThread
                :comments="comments"
                :article-slug="article.slug"
                :user-comment-votes="userCommentVotes"
            />
        </article>
    </PageWithSidebar>
</template>

<style scoped>
.article-show {
    width: 100%;
    max-width: none;
}
.hero-banner {
    width: 100%;
    max-height: 280px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 1.25rem;
}
.article-header { margin-bottom: 1.25rem; }
.article-header h1 {
    margin: 0 0 0.75rem;
    font-size: clamp(2.25rem, 5vw, 3.25rem);
    line-height: 1.15;
    color: var(--page-text, #1a202c);
}
.byline {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 1.2rem;
    color: var(--page-text, #4a5568);
}
.author-link {
    font-weight: 600;
    font-size: 1.25rem;
    color: inherit;
    text-decoration: none;
}
.author-link:hover {
    text-decoration: underline;
    opacity: 0.85;
}
.article-date { font-size: 1.1rem; }
.dot { opacity: 0.45; }
.book-cover {
    aspect-ratio: 9/16;
    max-width: 200px;
    object-fit: cover;
    float: right;
    margin: 0 0 1rem 1.25rem;
    border-radius: 6px;
}
.article-content {
    line-height: 1.8;
    overflow: hidden;
    margin-bottom: 1.5rem;
    font-size: 1.05rem;
}
.article-content :deep(.content-image-float) {
    float: right;
    max-width: 42%;
    margin: 0 0 1rem 1.5rem;
    border-radius: 6px;
}
.article-divider {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 2rem 0;
}
.article-actions {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}
.edit-btn, .delete-btn {
    padding: 8px 14px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    color: #fff;
    font-size: 0.875rem;
}
.edit-btn { background: #4a90e2; }
.delete-btn { background: #f44336; }
.back-link {
    display: inline-block;
    margin: 1.5rem 0 2rem;
    font-weight: 500;
    color: #4299e1;
    text-decoration: none;
}
.back-link:hover { color: #3182ce; }
[data-theme="dark"] .article-divider { border-color: #404040; }
</style>
