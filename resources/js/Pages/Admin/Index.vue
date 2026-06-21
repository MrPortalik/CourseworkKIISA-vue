<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'
import ArticleCoverThumb from '@/Components/Articles/ArticleCoverThumb.vue'
import { ref } from 'vue'

defineProps({
    pendingArticles: Object,
    allDrafts: Object,
    canViewAllDrafts: { type: Boolean, default: true },
})

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('ru-RU') : '')

const rejectTarget = ref(null)
const rejectForm = useForm({ reason: '' })

const openReject = (article) => {
    rejectTarget.value = article
    rejectForm.reason = ''
}

const closeReject = () => {
    rejectTarget.value = null
    rejectForm.reset()
}

const submitReject = () => {
    if (!rejectTarget.value) return
    rejectForm.post(route('admin.articles.reject', rejectTarget.value.slug), {
        preserveScroll: true,
        onSuccess: closeReject,
    })
}

const approve = (article) => {
    router.post(route('admin.articles.approve', article.slug), {}, { preserveScroll: true })
}
</script>

<template>
    <PageHead
        title="Админ-панель"
        description="Модерация статей и управление публикациями на портале КИИСА."
    />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <h2>Ожидают публикации</h2>
            <div v-if="pendingArticles.data.length" class="articles-list">
                <article v-for="article in pendingArticles.data" :key="article.id" class="admin-card">
                    <div class="cover-preview">
                        <ArticleCoverThumb :src="article.banner" alt="" />
                    </div>
                    <div class="card-body">
                        <h3>{{ article.title }}</h3>
                        <p class="meta">{{ article.user?.name }} · {{ formatDate(article.created_at) }}</p>
                        <span class="badge badge--publishable">Публикуется</span>
                    </div>
                    <div class="actions">
                        <Link :href="route('articles.show', article.slug)" class="btn">Просмотр</Link>
                        <button type="button" class="btn btn--success" @click="approve(article)">Принять</button>
                        <button type="button" class="btn btn--danger" @click="openReject(article)">Отклонить</button>
                    </div>
                </article>
            </div>
            <p v-else class="empty">Нет статей на модерации</p>
        </div>

        <div v-if="canViewAllDrafts && allDrafts" class="section">
            <h2>Все неопубликованные</h2>
            <div v-if="allDrafts.data.length" class="articles-list">
                <article v-for="article in allDrafts.data" :key="article.id" class="admin-card">
                    <div class="cover-preview">
                        <ArticleCoverThumb :src="article.banner" alt="" />
                    </div>
                    <div class="card-body">
                        <h3>{{ article.title }}</h3>
                        <p class="meta">{{ article.user?.name }} · {{ formatDate(article.created_at) }}</p>
                        <span v-if="article.is_publishable" class="badge badge--publishable">Публикуется</span>
                        <span v-else class="badge badge--draft">Черновик</span>
                    </div>
                    <div class="actions">
                        <Link :href="route('articles.show', article.slug)" class="btn">Просмотр</Link>
                        <Link :href="route('articles.edit', article.slug)" class="btn btn--primary">Редактировать</Link>
                    </div>
                </article>
            </div>
            <p v-else class="empty">Нет черновиков</p>
        </div>

        <div v-if="rejectTarget" class="reject-overlay" @click.self="closeReject">
            <form class="reject-modal" @submit.prevent="submitReject">
                <h3>Отклонить публикацию</h3>
                <p class="reject-title">«{{ rejectTarget.title }}»</p>
                <label class="reject-label">Причина (будет отправлена автору)</label>
                <textarea v-model="rejectForm.reason" rows="4" required class="reject-textarea" />
                <div class="reject-actions">
                    <button type="button" class="btn" @click="closeReject">Отмена</button>
                    <button type="submit" class="btn btn--danger" :disabled="rejectForm.processing">Отклонить</button>
                </div>
            </form>
        </div>
    </section>
</template>

<style scoped>
.admin-panel { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem 3rem; }
.admin-panel h1 { margin: 0 0 0.75rem; }
.admin-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
}
.section { margin-bottom: 2.5rem; }
.section h2 { margin: 0 0 0.75rem; }
.articles-list { display: flex; flex-direction: column; gap: 1rem; }
.admin-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
.cover-preview {
    width: 72px;
    flex-shrink: 0;
}
.cover-preview :deep(.article-cover-thumb) {
    width: 72px;
    max-width: 72px;
}
.card-body { flex: 1; }
.meta { color: #718096; font-size: 0.875rem; }
.badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; }
.badge--publishable { background: #fef9c3; color: #a16207; }
.badge--draft { background: #fed7d7; color: #c53030; }
.actions { display: flex; gap: 0.5rem; flex-shrink: 0; flex-wrap: wrap; }
.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    background: #edf2f7;
    color: #2d3748;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    font-family: inherit;
}
.btn--primary { background: #4299e1; color: #fff; }
.btn--success { background: #48bb78; color: #fff; }
.btn--danger { background: #f56565; color: #fff; }
.empty { text-align: center; color: #718096; padding: 2rem; background: #f7fafc; border-radius: 8px; }
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
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}
.reject-modal h3 { margin: 0 0 0.5rem; }
.reject-title { color: #718096; margin: 0 0 1rem; font-size: 0.95rem; }
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
[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .admin-card { background: var(--theme_black); border-color: #333; }
[data-theme="dark"] .admin-card h3 { color: #f0f0f0; }
[data-theme="dark"] .meta { color: #aaa; }
[data-theme="dark"] .btn:not(.btn--success):not(.btn--danger):not(.btn--primary) {
    background: #2a2a2a;
    color: #f0f0f0;
    border: 1px solid #404040;
}
[data-theme="dark"] .btn--success {
    background: #48bb78;
    color: #ffffff;
    border: none;
}
[data-theme="dark"] .btn--danger {
    background: #f56565;
    color: #ffffff;
    border: none;
}
[data-theme="dark"] .btn--primary {
    background: #4299e1;
    color: #ffffff;
    border: none;
}
[data-theme="dark"] .empty { background: #141414; border: 1px solid #333; color: #aaa; }
[data-theme="dark"] .reject-modal { background: #141414; color: #f0f0f0; }
[data-theme="dark"] .reject-textarea { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }

@media (max-width: 768px) {
    .admin-panel {
        margin: 1rem auto;
        padding: 0 1rem 2rem;
    }

    .admin-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .admin-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .actions {
        width: 100%;
    }

    .actions .btn {
        flex: 1 1 auto;
        text-align: center;
        justify-content: center;
    }
}
</style>
