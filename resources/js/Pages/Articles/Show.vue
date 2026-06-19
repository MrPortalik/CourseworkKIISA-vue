<script setup>
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import StarRating from '@/Components/StarRating.vue'
import CommentThread from '@/Components/Comments/CommentThread.vue'
import TagWithTooltip from '@/Components/UI/TagWithTooltip.vue'
import PartialStar from '@/Components/UI/PartialStar.vue'
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
    previousArticleSlug: { type: String, default: null },
    nextArticleSlug: { type: String, default: null },
    randomArticleSlug: { type: String, default: null },
})

const page = usePage()

const formatDate = (d) =>
    d ? new Date(d).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''

const isAdmin = computed(() => ['admin', 'owner'].includes(page.props.auth?.user?.role))
const canModerate = computed(() => ['admin', 'owner', 'moderator'].includes(page.props.auth?.user?.role))

const isCoauthor = computed(() =>
    props.article.coauthors?.some((c) => c.id === page.props.auth?.user?.id),
)

const canModerateThis = computed(() => {
    if (!canModerate.value) return false
    if (isAdmin.value) return true
    return props.article.is_publishable || props.article.is_published
})

const canEdit = computed(() => {
    const u = page.props.auth?.user
    return u && (u.id === props.article.user_id || isCoauthor.value || canModerateThis.value)
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

const showReportModal = ref(false)
const isLoggedIn = computed(() => !!page.props.auth?.user)
const canReport = computed(() => isLoggedIn.value && props.article.is_published)

const headerRatingValue = computed(() => (
    props.article.ratings_count > 0 ? Number(props.article.average_rating) : 0
))

const scrollToRating = () => {
    document.getElementById('article-rating')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}
</script>

<template>
    <PageHead
        :title="article.title"
        :description="metaDescription || `Статья «${article.title}» на информационном портале КИИСА.`"
    />

    <PageWithSidebar>
        <article class="article-show">
            <div v-if="article.hero_banner" class="article-hero">
                <img :src="article.hero_banner" :alt="article.title" class="article-hero-img" />
            </div>

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
                        <PartialStar :rating="headerRatingValue" size="1.75rem" />
                        <span class="header-rating-avg">{{ formatRating(headerRatingValue) }}</span>
                        <button type="button" class="rate-link content-link" @click="scrollToRating">Оценить →</button>
                    </div>
                </div>
                <div v-if="article.tags?.length" class="article-tags">
                    <TagWithTooltip v-for="tag in article.tags" :key="tag.id" :tag="tag" />
                </div>
            </header>

            <div v-if="canModerateThis && pendingModeration" class="moderation-bar">
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
                :previous-article-slug="previousArticleSlug"
                :next-article-slug="nextArticleSlug"
                :random-article-slug="randomArticleSlug"
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
.article-hero {
    margin: -0.5rem 0 1.5rem;
    border-radius: 10px;
    overflow: hidden;
    line-height: 0;
}
.article-hero-img {
    display: block;
    width: 100%;
    max-height: 320px;
    object-fit: cover;
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
    margin-right: 0.35rem;
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
.header-rating-avg {
    font-size: 0.95rem;
    font-weight: 700;
    color: #4a5568;
}
[data-theme="dark"] .header-rating-avg {
    color: #f0f0f0;
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
    font-size: inherit;
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
    max-height: calc(280px * var(--image-scale, 1));
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
[data-theme="dark"] .edit-btn {
    background: transparent;
    color: #4a90e2;
    border: 2px solid #4a90e2;
}
[data-theme="dark"] .delete-btn {
    background: transparent;
    color: #f44336;
    border: 2px solid #f44336;
}
[data-theme="dark"] .edit-btn:hover {
    background: rgba(74, 144, 226, 0.12);
}
[data-theme="dark"] .delete-btn:hover {
    background: rgba(244, 67, 54, 0.12);
}
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
.report-link {
    background: #e53e3e;
    color: #ffffff;
    border: 2px solid #e53e3e;
    border-radius: 18px;
    padding: 0.45rem 1rem;
    font-weight: 600;
    font-size: 0.875rem;
}
.report-link:hover {
    background: #c53030;
    border-color: #c53030;
    color: #ffffff;
    text-decoration: none;
}
[data-theme="dark"] .report-link {
    background: #e53e3e;
    color: #ffffff;
    border-color: #e53e3e;
}
[data-theme="dark"] .report-link:hover {
    background: #c53030;
    border-color: #c53030;
    color: #ffffff;
}
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
[data-theme="dark"] .mod-btn:not(.mod-btn--approve):not(.mod-btn--reject) {
    background: #2a2a2a;
    color: #f0f0f0;
}
[data-theme="dark"] .mod-btn--approve {
    background: #48bb78;
    color: #ffffff;
}
[data-theme="dark"] .mod-btn--reject {
    background: #f56565;
    color: #ffffff;
}

@media (max-width: 768px) {
    .article-content :deep(.content-image-figure),
    .article-content :deep(.content-image-float) {
        float: none !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 0 1rem 0 !important;
    }

    .byline {
        flex-direction: column;
        align-items: stretch;
        gap: 0.65rem;
    }

    .author-link {
        font-size: 1.05rem;
    }

    .article-date {
        font-size: 0.95rem;
    }
}
</style>
