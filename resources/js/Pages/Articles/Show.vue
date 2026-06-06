<script setup>
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import StarRating from '@/Components/StarRating.vue'
import CommentThread from '@/Components/Comments/CommentThread.vue'
import TagWithTooltip from '@/Components/UI/TagWithTooltip.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import FeedbackModal from '@/Components/FeedbackModal.vue'
import { computed, ref } from 'vue'
import { formatRating } from '@/lib/formatRating'

const props = defineProps({
    article: Object,
    metaDescription: { type: String, default: '' },
    comments: Array,
    userRating: Number,
    userCommentVotes: Object,
    canRate: { type: Boolean, default: true },
})

const page = usePage()

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''

const isAdmin = computed(() => ['admin', 'owner'].includes(page.props.auth?.user?.role))

const canEdit = computed(() => {
    const u = page.props.auth?.user
    return u && (u.id === props.article.user_id || isAdmin.value)
})

const pendingModeration = computed(
    () => props.article.is_publishable && !props.article.is_published,
)

const showRejectModal = ref(false)
const rejectForm = useForm({ reason: '' })

const approveArticle = () => {
    router.post(route('admin.articles.approve', props.article.slug), {}, { preserveScroll: true })
}

const openReject = () => {
    rejectForm.reason = ''
    showRejectModal.value = true
}

const closeReject = () => {
    showRejectModal.value = false
    rejectForm.reset()
}

const submitReject = () => {
    rejectForm.post(route('admin.articles.reject', props.article.slug), {
        preserveScroll: true,
        onSuccess: closeReject,
    })
}

const deleteArticle = () => {
    if (confirm('Удалить статью?')) router.delete(route('articles.destroy', props.article.slug))
}

const headerStarRating = computed(() => {
    if (props.userRating != null) return props.userRating
    if (props.article.ratings_count > 0) return Math.round(Number(props.article.average_rating))
    return 0
})

const scrollToRating = () => {
    document.getElementById('article-rating')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

const showReportModal = ref(false)
const isLoggedIn = computed(() => !!page.props.auth?.user)
const canReport = computed(() => isLoggedIn.value && props.article.is_published)
</script>

<template>
    <PageHead
        :title="article.title"
        :description="metaDescription || `Статья «${article.title}» на информационном портале КИИСА.`"
    />

    <PageWithSidebar>
        <article class="article-show">
            <header class="article-header">
                <h1>{{ article.title }}</h1>
                <div class="byline">
                    <div class="byline-main">
                        <span class="authors-line">
                            <Link :href="route('authors.show', article.user_id)" class="author-link">
                                {{ article.user?.name }}
                            </Link>
                            <template v-if="article.coauthors?.length">
                                <span class="dot">·</span>
                                <span class="coauthors-prefix">со-авторы:</span>
                                <template v-for="(co, idx) in article.coauthors" :key="co.id">
                                    <span v-if="idx > 0" class="comma">, </span>
                                    <Link :href="route('authors.show', co.id)" class="author-link">{{ co.name }}</Link>
                                </template>
                            </template>
                        </span>
                        <span class="dot">·</span>
                        <time class="article-date">{{ formatDate(article.created_at) }}</time>
                    </div>
                    <div class="byline-rating">
                        <div class="header-stars" aria-label="Оценка статьи">
                            <span
                                v-for="n in 5"
                                :key="n"
                                class="header-star"
                                :class="{ 'header-star--filled': headerStarRating >= n }"
                            >
                                ★
                            </span>
                        </div>
                        <span v-if="article.ratings_count" class="header-rating-avg">
                            {{ formatRating(article.average_rating) }}
                        </span>
                        <button type="button" class="rate-link content-link" @click="scrollToRating">Оценить →</button>
                    </div>
                </div>
                <div v-if="article.tags?.length" class="article-tags">
                    <TagWithTooltip v-for="tag in article.tags" :key="tag.id" :tag="tag" />
                </div>
            </header>

            <div v-if="isAdmin && pendingModeration" class="moderation-bar">
                <span class="moderation-label">Статья ожидает публикации</span>
                <div class="moderation-actions">
                    <button type="button" class="mod-btn mod-btn--approve" @click="approveArticle">Принять</button>
                    <button type="button" class="mod-btn mod-btn--reject" @click="openReject">Отклонить</button>
                </div>
            </div>

            <div v-if="canEdit" class="article-actions">
                <Link :href="route('articles.edit', article.slug)" class="edit-btn">Редактировать</Link>
                <button type="button" class="delete-btn" @click="deleteArticle">Удалить</button>
            </div>

            <div class="article-content" v-html="article.content" />

            <hr class="article-divider" />

            <StarRating
                :article-slug="article.slug"
                :user-rating="userRating"
                :average-rating="article.average_rating"
                :ratings-count="article.ratings_count"
                :can-rate="canRate"
            />

            <CommentThread
                :comments="comments"
                :article-slug="article.slug"
                :article-author-id="article.user_id"
                :user-comment-votes="userCommentVotes"
            />

            <div class="article-footer">
                <Link :href="route('articles.index')" class="back-link">← Назад к статьям</Link>
                <button v-if="canReport" type="button" class="report-link" @click="showReportModal = true">
                    Пожаловаться
                </button>
            </div>

            <FeedbackModal
                :open="showReportModal"
                type="article_complaint"
                :article-id="article.id"
                @close="showReportModal = false"
            />
        </article>

        <div v-if="showRejectModal" class="reject-overlay" @click.self="closeReject">
            <form class="reject-modal" @submit.prevent="submitReject">
                <h3>Отклонить публикацию</h3>
                <label class="reject-label">Причина (будет отправлена автору)</label>
                <textarea v-model="rejectForm.reason" rows="4" required class="reject-textarea" />
                <div class="reject-actions">
                    <button type="button" class="mod-btn" @click="closeReject">Отмена</button>
                    <button type="submit" class="mod-btn mod-btn--reject" :disabled="rejectForm.processing">Отклонить</button>
                </div>
            </form>
        </div>
    </PageWithSidebar>
</template>

<style scoped>
.article-show {
    width: 100%;
    max-width: none;
}
.article-header { margin-bottom: 1.25rem; }
.article-header h1 {
    margin: 0 0 0.75rem;
    font-size: clamp(2.25rem, 5vw, 3.25rem);
    line-height: 1.15;
    color: var(--page-text, #1a202c);
}
.authors-line { display: inline; }
.coauthors-prefix {
    margin-left: 0.15rem;
    color: #718096;
    font-size: 0.95em;
}
.byline {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem 1.5rem;
    font-size: 1.2rem;
    color: var(--page-text, #4a5568);
}
.byline-main {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.5rem;
    min-width: 0;
    flex: 1;
}
.byline-rating {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-shrink: 0;
}
.header-stars {
    display: inline-flex;
    gap: 0.15rem;
    line-height: 1;
}
.header-star {
    font-size: 1.75rem;
    color: #cbd5e0;
}
.header-star--filled {
    color: #f6ad55;
}
.header-rating-avg {
    font-size: 0.95rem;
    font-weight: 600;
    color: #718096;
}
.rate-link {
    white-space: nowrap;
    font-size: 0.95rem;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-family: inherit;
}
.article-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.85rem;
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
.moderation-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
}
.moderation-label { font-weight: 600; color: #a16207; }
.moderation-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.mod-btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-family: inherit;
    font-size: 0.875rem;
    background: #edf2f7;
    color: #2d3748;
}
.mod-btn--approve { background: #48bb78; color: #fff; }
.mod-btn--reject { background: #f56565; color: #fff; }
.article-content {
    line-height: 1.8;
    margin-bottom: 1.5rem;
    font-size: 1.05rem;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
    display: flow-root;
    overflow-x: hidden;
}
.article-content :deep(p) {
    margin: 0 0 1.25rem;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
}
.article-content :deep(hr.article-content-divider) {
    border: none;
    border-top: 1px solid #e2e8f0;
    margin: 1.5rem 0;
    width: 100%;
    clear: both;
}
.article-content :deep(img) {
    max-width: 100%;
    height: auto;
    box-sizing: border-box;
}
.article-content :deep(.content-image-figure) {
    float: right;
    width: 20%;
    max-width: 20%;
    margin: 0 0 0.75rem 0.75rem;
    box-sizing: border-box;
}
.article-content :deep(.content-image-figure img) {
    display: block;
    width: 100%;
    max-width: 100%;
    height: auto;
    max-height: 280px;
    border-radius: 6px;
    float: none;
    margin: 0;
    object-fit: contain;
}
.article-content :deep(.content-image-float) {
    float: right;
    width: 20%;
    max-width: 20%;
    margin: 0 0 0.75rem 0.75rem;
    border-radius: 6px;
    box-sizing: border-box;
    object-fit: contain;
}
.article-content :deep(.article-content-end) {
    clear: both;
    height: 0;
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
.article-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 2rem;
    padding-bottom: 1rem;
}
.back-link,
.report-link {
    font-weight: 500;
    font-size: 1rem;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-family: inherit;
}
.back-link { color: #4299e1; }
.back-link:hover { color: #3182ce; }
.report-link { color: #e53e3e; }
.report-link:hover { color: #c53030; text-decoration: underline; }
.reject-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 1rem;
}
.reject-modal {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem;
    width: min(480px, 100%);
}
.reject-modal h3 { margin: 0 0 0.75rem; }
.reject-label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
.reject-textarea {
    width: 100%;
    padding: 0.65rem;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    box-sizing: border-box;
    margin-bottom: 1rem;
    font-family: inherit;
}
.reject-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
[data-theme="dark"] .article-divider,
[data-theme="dark"] .article-content :deep(hr.article-content-divider) {
    border-color: #404040;
}
[data-theme="dark"] .moderation-bar {
    background: rgba(254, 249, 195, 0.1);
    border-color: rgba(253, 224, 71, 0.35);
}
[data-theme="dark"] .moderation-label { color: #fde047; }
[data-theme="dark"] .reject-modal { background: #141414; color: #f0f0f0; }
[data-theme="dark"] .reject-textarea { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .mod-btn { background: #2a2a2a; color: #f0f0f0; }

@media (max-width: 768px) {
    .byline {
        flex-direction: column;
        align-items: stretch;
        gap: 0.65rem;
    }

    .byline-rating {
        width: 100%;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 0.35rem 0.5rem;
    }

    .header-star {
        font-size: 1.25rem;
    }

    .author-link {
        font-size: 1.05rem;
    }

    .article-date {
        font-size: 0.95rem;
    }
}
</style>
