<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { ref } from 'vue'

defineProps({
    reports: Object,
    pendingCount: Number,
})

const respondTarget = ref(null)
const respondForm = useForm({ admin_reply: '' })

const openRespond = (report) => {
    respondTarget.value = report
    respondForm.admin_reply = report.admin_reply || ''
}

const closeRespond = () => {
    respondTarget.value = null
    respondForm.reset()
}

const submitRespond = () => {
    if (!respondTarget.value) return
    respondForm.post(route('admin.reports.respond', respondTarget.value.id), {
        preserveScroll: true,
        onSuccess: closeRespond,
    })
}

const typeLabel = (type) => (type === 'article_complaint' ? 'Жалоба на статью' : 'Обратная связь')
const formatDate = (d) => (d ? new Date(d).toLocaleString('ru-RU') : '')
</script>

<template>
    <PageHead
        title="Жалобы и предложения"
        description="Раздел жалоб и обратной связи в админ-панели портала КИИСА."
    />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Жалобы и предложения</h1>
            <nav class="admin-nav">
                <Link :href="route('admin.index')" class="admin-tab">Модерация</Link>
                <Link :href="route('admin.users.index')" class="admin-tab">Пользователи</Link>
                <Link :href="route('admin.categories')" class="admin-tab">Категории</Link>
                <Link :href="route('admin.taxonomy')" class="admin-tab">Теги</Link>
            </nav>
        </div>

        <p class="summary">Ожидают рассмотрения: {{ pendingCount }}</p>

        <div v-if="reports.data.length" class="reports-list">
            <article v-for="report in reports.data" :key="report.id" class="report-card">
                <div class="report-head">
                    <span class="badge" :class="report.status === 'pending' ? 'badge--pending' : 'badge--resolved'">
                        {{ report.status === 'pending' ? 'Новое' : 'Рассмотрено' }}
                    </span>
                    <span class="type">{{ typeLabel(report.type) }}</span>
                    <span class="meta">{{ report.user?.name }} · {{ formatDate(report.created_at) }}</span>
                </div>
                <p class="message">{{ report.message }}</p>
                <div v-if="report.article" class="article-link-wrap">
                    <Link :href="route('articles.show', report.article.slug)" class="article-link">
                        Статья: {{ report.article.title }}
                    </Link>
                </div>
                <div v-if="report.admin_reply" class="reply-block">
                    <p class="reply-label">Ответ ({{ report.responded_by?.name }}, {{ formatDate(report.responded_at) }})</p>
                    <p>{{ report.admin_reply }}</p>
                </div>
                <button type="button" class="btn" @click="openRespond(report)">
                    {{ report.admin_reply ? 'Изменить ответ' : 'Ответить' }}
                </button>
            </article>
        </div>
        <p v-else class="empty">Жалоб и предложений пока нет</p>

        <nav v-if="reports.links?.length > 3" class="pagination">
            <Link
                v-for="(link, index) in reports.links"
                :key="index"
                :href="link.url || '#'"
                class="page-link"
                :class="{ active: link.active, disabled: !link.url }"
                v-html="link.label"
            />
        </nav>
    </section>

    <div v-if="respondTarget" class="overlay" @click.self="closeRespond">
        <div class="overlay-card">
            <h3>Ответ пользователю</h3>
            <textarea v-model="respondForm.admin_reply" rows="6" class="reply-input" placeholder="Текст ответа" />
            <p v-if="respondForm.errors.admin_reply" class="error">{{ respondForm.errors.admin_reply }}</p>
            <div class="overlay-actions">
                <button type="button" class="btn-secondary" @click="closeRespond">Отмена</button>
                <button type="button" class="btn-primary" :disabled="respondForm.processing" @click="submitRespond">
                    Отправить
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-panel { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
.admin-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
.admin-nav { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.admin-tab { padding: 0.5rem 1rem; border-radius: 8px; background: #edf2f7; text-decoration: none; color: #2d3748; font-weight: 600; }
.summary { color: #718096; margin-bottom: 1.5rem; }
.reports-list { display: grid; gap: 1rem; }
.report-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.25rem; }
.report-head { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; align-items: center; margin-bottom: 0.75rem; }
.badge { font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 999px; }
.badge--pending { background: #fef9c3; color: #a16207; }
.badge--resolved { background: #dcfce7; color: #166534; }
.type { font-weight: 600; }
.meta { color: #718096; font-size: 0.875rem; }
.message { white-space: pre-wrap; line-height: 1.55; margin: 0 0 0.75rem; }
.article-link { color: #0db7ff; font-weight: 600; }
.reply-block { background: #f7fafc; border-radius: 8px; padding: 0.75rem; margin: 0.75rem 0; }
.reply-label { font-weight: 600; margin: 0 0 0.35rem; font-size: 0.875rem; color: #4a5568; }
.btn { margin-top: 0.5rem; background: #0db7ff; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; cursor: pointer; }
.empty { color: #718096; }
.pagination { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.5rem; }
.page-link { padding: 0.5rem 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; text-decoration: none; color: #4a5568; }
.page-link.active { background: #4299e1; color: #fff; }
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 1rem; }
.overlay-card { background: #fff; border-radius: 12px; padding: 1.25rem; width: min(520px, 100%); }
.reply-input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 8px; font: inherit; }
.overlay-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; }
.btn-primary, .btn-secondary { border: none; border-radius: 8px; padding: 0.55rem 1rem; font-weight: 600; cursor: pointer; }
.btn-primary { background: #0db7ff; color: #fff; }
.btn-secondary { background: #edf2f7; color: #2d3748; }
.error { color: #c53030; font-size: 0.875rem; }
[data-theme="dark"] .report-card, [data-theme="dark"] .overlay-card { background: #141414; border-color: #333; color: #f0f0f0; }
[data-theme="dark"] .reply-block { background: #1f1f1f; }
[data-theme="dark"] .admin-tab { background: #2a2a2a; color: #f0f0f0; }
</style>
