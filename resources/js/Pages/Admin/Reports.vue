<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { ref } from 'vue'

defineProps({
    reports: Object,
    pendingCount: Number,
})

const showModal = ref(true)
const respondTarget = ref(null)
const respondForm = useForm({ admin_reply: '' })

const close = () => {
    showModal.value = false
    router.visit(route('admin.index'))
}

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

    <div v-if="showModal" class="modal-overlay">
        <div class="modal-window modal-window--wide" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h2>Жалобы и предложения</h2>
                <button type="button" class="close-x" aria-label="Закрыть" @click="close">×</button>
            </div>

            <p class="summary">Ожидают рассмотрения: {{ pendingCount }}</p>

            <div v-if="reports.data.length" class="reports-list">
                <article v-for="report in reports.data" :key="report.id" class="report-row">
                    <div class="report-head">
                        <span class="badge" :class="report.status === 'pending' ? 'badge--pending' : 'badge--resolved'">
                            {{ report.status === 'pending' ? 'Новое' : 'Рассмотрено' }}
                        </span>
                        <span class="type">{{ typeLabel(report.type) }}</span>
                        <span class="meta">{{ report.user?.name }} · {{ formatDate(report.created_at) }}</span>
                    </div>
                    <p class="message">{{ report.message }}</p>
                    <Link
                        v-if="report.article"
                        :href="route('articles.show', report.article.slug)"
                        class="article-link"
                    >
                        Статья: {{ report.article.title }}
                    </Link>
                    <div v-if="report.admin_reply" class="reply-block">
                        <p class="reply-label">Ответ ({{ report.responded_by?.name }})</p>
                        <p>{{ report.admin_reply }}</p>
                    </div>
                    <button type="button" class="btn-row" @click="openRespond(report)">
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

            <button type="button" class="btn-close" @click="close">Закрыть</button>
        </div>
    </div>

    <div v-if="respondTarget" class="modal-overlay modal-overlay--nested" @click.self="closeRespond">
        <div class="modal-window modal-window--reply" role="dialog">
            <div class="modal-header">
                <h2>Ответ пользователю</h2>
                <button type="button" class="close-x" aria-label="Закрыть" @click="closeRespond">×</button>
            </div>
            <textarea v-model="respondForm.admin_reply" rows="6" class="reply-input" placeholder="Текст ответа" />
            <p v-if="respondForm.errors.admin_reply" class="error">{{ respondForm.errors.admin_reply }}</p>
            <div class="reply-actions">
                <button type="button" class="btn-close" @click="closeRespond">Отмена</button>
                <button type="button" class="btn-add btn-accent" :disabled="respondForm.processing" @click="submitRespond">
                    Отправить
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-overlay--nested { z-index: 220; }
.modal-window {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 560px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}
.modal-window--wide { max-width: 640px; }
.modal-window--reply { max-width: 480px; }
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.modal-header h2 { margin: 0; font-size: 1.35rem; }
.close-x {
    background: none;
    border: none;
    font-size: 1.75rem;
    cursor: pointer;
    line-height: 1;
    padding: 0 0.25rem;
}
.summary { color: #718096; margin: 0 0 1rem; font-size: 0.9rem; }
.reports-list { display: grid; gap: 0.75rem; margin-bottom: 1rem; }
.report-row {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem 1rem;
}
.report-head { display: flex; flex-wrap: wrap; gap: 0.4rem 0.75rem; align-items: center; margin-bottom: 0.5rem; }
.badge { font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; }
.badge--pending { background: #fef9c3; color: #a16207; }
.badge--resolved { background: #dcfce7; color: #166534; }
.type { font-weight: 600; font-size: 0.9rem; }
.meta { color: #718096; font-size: 0.8rem; }
.message { margin: 0 0 0.5rem; white-space: pre-wrap; line-height: 1.5; font-size: 0.95rem; }
.article-link { color: #0db7ff; font-weight: 600; font-size: 0.9rem; text-decoration: none; display: inline-block; margin-bottom: 0.5rem; }
.reply-block { background: #f7fafc; border-radius: 8px; padding: 0.65rem; margin: 0.5rem 0; font-size: 0.9rem; }
.reply-label { font-weight: 600; margin: 0 0 0.25rem; color: #4a5568; font-size: 0.8rem; }
.btn-row {
    margin-top: 0.35rem;
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 0.85rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
}
.empty { color: #718096; margin-bottom: 1rem; }
.pagination { display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 1rem; }
.page-link { padding: 0.4rem 0.7rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; text-decoration: none; color: #4a5568; font-size: 0.85rem; }
.page-link.active { background: #4299e1; color: #fff; }
.reply-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    box-sizing: border-box;
    margin-bottom: 0.75rem;
}
.reply-actions { display: flex; gap: 0.5rem; }
.btn-add {
    flex: 1;
    background: #4299e1;
    color: #fff;
    border: none;
    padding: 0.65rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.btn-close {
    width: 100%;
    padding: 0.65rem;
    background: #edf2f7;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.reply-actions .btn-close { width: auto; flex: 1; }
.error { color: #c53030; font-size: 0.875rem; margin: 0 0 0.5rem; }
[data-theme="dark"] .modal-window { background: #141414; border: 1px solid #333; color: #f0f0f0; }
[data-theme="dark"] .report-row { border-color: #333; }
[data-theme="dark"] .reply-block { background: #1f1f1f; }
[data-theme="dark"] .reply-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .btn-close { background: #2a2a2a; color: #f0f0f0; }
</style>
