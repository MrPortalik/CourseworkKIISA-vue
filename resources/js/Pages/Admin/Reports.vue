<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import PaginationNav from '@/Components/UI/PaginationNav.vue'
import ModalPanel from '@/Components/ModalPanel.vue'
import { ref } from 'vue'

const MESSAGE_PREVIEW_MAX = 280
const MESSAGE_LINE_CLAMP = 5

const props = defineProps({
    reports: Object,
    pendingCount: Number,
    filters: { type: Object, default: () => ({}) },
})

const respondTarget = ref(null)
const respondForm = useForm({ admin_reply: '' })
const deleteTarget = ref(null)
const deleteForm = useForm({ deletion_reason: '' })
const viewTarget = ref(null)
const typeFilter = ref(props.filters?.type || '')

const reportTypes = [
    { value: '', label: 'Все типы' },
    { value: 'feedback', label: 'Обратная связь' },
    { value: 'site_complaint', label: 'Жалоба на сайт' },
    { value: 'article_complaint', label: 'Жалоба на статью' },
    { value: 'user_complaint', label: 'Жалоба на пользователя' },
]

const applyTypeFilter = () => {
    router.get(route('admin.reports.index'), {
        type: typeFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true })
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

const openDelete = (report) => {
    deleteTarget.value = report
    deleteForm.deletion_reason = ''
}

const closeDelete = () => {
    deleteTarget.value = null
    deleteForm.reset()
}

const submitDelete = () => {
    if (!deleteTarget.value) return
    deleteForm.delete(route('admin.reports.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: closeDelete,
    })
}

const openView = (report) => {
    viewTarget.value = report
}

const closeView = () => {
    viewTarget.value = null
}

const isLongMessage = (message) => {
    if (!message) return false
    if (message.length > MESSAGE_PREVIEW_MAX) return true
    return message.split('\n').length > MESSAGE_LINE_CLAMP
}

const messagePreview = (message) => {
    if (!isLongMessage(message)) return message
    if (message.length > MESSAGE_PREVIEW_MAX) {
        return `${message.slice(0, MESSAGE_PREVIEW_MAX).trimEnd()}…`
    }
    return message
}

const typeLabel = (type) => {
    if (type === 'article_complaint') return 'Жалоба на статью'
    if (type === 'user_complaint') return 'Жалоба на пользователя'
    if (type === 'site_complaint') return 'Жалоба на сайт'
    return 'Обратная связь'
}

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
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <h2>Жалобы и предложения</h2>

            <div class="filters">
                <label class="filter-label" for="report-type-filter">Тип</label>
                <select
                    id="report-type-filter"
                    v-model="typeFilter"
                    class="filter-select"
                    @change="applyTypeFilter"
                >
                    <option v-for="option in reportTypes" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <p class="summary">Ожидают рассмотрения: {{ pendingCount }}</p>

            <div v-if="reports.data.length" class="reports-list">
                <article v-for="report in reports.data" :key="report.id" class="report-row">
                    <div class="report-layout">
                        <div class="report-main">
                            <div class="report-head">
                                <span class="badge" :class="report.status === 'pending' ? 'badge--pending' : 'badge--resolved'">
                                    {{ report.status === 'pending' ? 'Новое' : 'Рассмотрено' }}
                                </span>
                                <span class="type">{{ typeLabel(report.type) }}</span>
                            </div>

                            <div class="reporter-line">
                                <UserAvatar
                                    v-if="report.user"
                                    :src="report.user.avatar"
                                    :alt="report.user.name"
                                    :size="28"
                                />
                                <span class="meta">
                                    <template v-if="report.user">
                                        <span class="reporter-prefix">Жалоба от</span>
                                        <Link
                                            :href="route('authors.show', report.user.id)"
                                            class="reporter-link"
                                        >
                                            {{ report.user.name }}
                                        </Link>
                                    </template>
                                    <span v-else>Неизвестный</span>
                                    · {{ formatDate(report.created_at) }}
                                </span>
                            </div>

                            <div class="message-block">
                                <p
                                    class="message"
                                    :class="{ 'message--clamped': isLongMessage(report.message) }"
                                >
                                    {{ messagePreview(report.message) }}
                                </p>
                                <button
                                    v-if="isLongMessage(report.message)"
                                    type="button"
                                    class="expand-btn"
                                    @click="openView(report)"
                                >
                                    Развернуть
                                </button>
                            </div>

                            <div v-if="report.attachments?.length" class="attachments-block">
                                <p class="attachments-label">Вложения</p>
                                <div class="attachments-grid">
                                    <a
                                        v-for="(file, index) in report.attachments"
                                        :key="`${file.path}-${index}`"
                                        :href="file.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="attachment-thumb"
                                    >
                                        <img :src="file.url" :alt="file.name || 'Вложение'" loading="lazy" />
                                    </a>
                                </div>
                            </div>

                            <div v-if="report.admin_reply" class="reply-block">
                                <p class="reply-label">Ответ ({{ report.responded_by?.name }})</p>
                                <p>{{ report.admin_reply }}</p>
                            </div>

                            <div class="report-footer">
                                <button type="button" class="btn-row" @click="openRespond(report)">
                                    {{ report.admin_reply ? 'Изменить ответ' : 'Ответить' }}
                                </button>
                                <button type="button" class="btn-row btn-row--danger" @click="openDelete(report)">
                                    Удалить
                                </button>
                                <Link
                                    v-if="report.article"
                                    :href="route('articles.show', report.article.slug)"
                                    class="subject-link subject-link--inline"
                                >
                                    Статья: {{ report.article.title }}
                                </Link>
                            </div>
                        </div>

                        <aside v-if="report.reported_user" class="reported-user-card">
                            <UserAvatar
                                :src="report.reported_user.avatar"
                                :alt="report.reported_user.name"
                                :size="40"
                            />
                            <div class="reported-user-info">
                                <p class="reported-user-label">На пользователя</p>
                                <Link
                                    :href="route('authors.show', report.reported_user.id)"
                                    class="reported-user-name"
                                >
                                    {{ report.reported_user.name }}
                                </Link>
                            </div>
                        </aside>
                    </div>
                </article>
            </div>
            <p v-else class="empty">Жалоб и предложений пока нет</p>

            <PaginationNav :links="reports.links" />
        </div>
    </section>

    <ModalPanel
        :open="!!viewTarget"
        title="Текст жалобы"
        @close="closeView"
    >
        <template v-if="viewTarget">
            <p class="view-meta">
                {{ typeLabel(viewTarget.type) }}
                <template v-if="viewTarget.user"> · {{ viewTarget.user.name }}</template>
                · {{ formatDate(viewTarget.created_at) }}
            </p>
            <p class="view-message">{{ viewTarget.message }}</p>
            <div v-if="viewTarget.attachments?.length" class="attachments-block">
                <p class="attachments-label">Вложения</p>
                <div class="attachments-grid">
                    <a
                        v-for="(file, index) in viewTarget.attachments"
                        :key="`${file.path}-${index}`"
                        :href="file.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="attachment-thumb"
                    >
                        <img :src="file.url" :alt="file.name || 'Вложение'" loading="lazy" />
                    </a>
                </div>
            </div>
        </template>
    </ModalPanel>

    <ModalPanel
        :open="!!deleteTarget"
        title="Удалить жалобу"
        @close="closeDelete"
    >
        <form id="delete-report-form" @submit.prevent="submitDelete">
            <label class="form-label" for="deletion-reason">Причина удаления</label>
            <textarea
                id="deletion-reason"
                v-model="deleteForm.deletion_reason"
                class="reply-input"
                rows="5"
                required
                minlength="5"
                placeholder="Укажите, почему жалоба удаляется"
            />
            <p v-if="deleteForm.errors.deletion_reason" class="error">{{ deleteForm.errors.deletion_reason }}</p>
        </form>
        <template #footer>
            <button type="button" class="btn-close" @click="closeDelete">Отмена</button>
            <button type="submit" form="delete-report-form" class="btn-delete" :disabled="deleteForm.processing">
                Удалить
            </button>
        </template>
    </ModalPanel>

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
.admin-panel { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem 3rem; }
.admin-panel h1 { margin: 0 0 0.75rem; }
.admin-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    margin-bottom: 2rem;
}
.section { margin-bottom: 2.5rem; }
.section h2 { margin: 0 0 0.75rem; }
.filters {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1rem;
}
.filter-label {
    font-weight: 600;
    font-size: 0.9rem;
}
.filter-select {
    flex: 1;
    max-width: 320px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    background: #fff;
}
.summary { color: #718096; margin: 0 0 1rem; font-size: 0.9rem; }
.reports-list { display: grid; gap: 0.75rem; margin-bottom: 1rem; }
.report-row {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem 1rem;
}
.report-layout {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}
.report-main { flex: 1; min-width: 0; }
.report-head { display: flex; flex-wrap: wrap; gap: 0.4rem 0.75rem; align-items: center; margin-bottom: 0.5rem; }
.badge { font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; }
.badge--pending { background: #fef9c3; color: #a16207; }
.badge--resolved { background: #dcfce7; color: #166534; }
.type { font-weight: 600; font-size: 0.9rem; }
.reporter-line {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.meta { color: #718096; font-size: 0.8rem; }
.reporter-prefix { margin-right: 0.25rem; }
.reporter-link {
    color: #0db7ff;
    font-weight: 600;
    text-decoration: none;
}
.reporter-link:hover { text-decoration: underline; }
.message-block { margin: 0 0 0.5rem; }
.message {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.5;
    font-size: 0.95rem;
}
.message--clamped {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 5;
    overflow: hidden;
}
.expand-btn {
    margin-top: 0.35rem;
    padding: 0;
    border: none;
    background: none;
    color: #0db7ff;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: underline;
}
.expand-btn:hover { color: #0a9ad9; }
.view-meta {
    margin: 0 0 0.75rem;
    color: #718096;
    font-size: 0.85rem;
}
.view-message {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.55;
    font-size: 0.95rem;
}
.form-label {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 600;
    font-size: 0.9rem;
}
.attachments-block { margin: 0.5rem 0 0.75rem; }
.attachments-label {
    margin: 0 0 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #718096;
}
.attachments-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.attachment-thumb {
    display: block;
    width: 88px;
    height: 88px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.attachment-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
[data-theme="dark"] .attachments-label { color: #aaa; }
[data-theme="dark"] .attachment-thumb { border-color: #404040; }
.reply-block {
    background: #f7fafc;
    border-radius: 8px;
    padding: 0.65rem;
    margin: 0.5rem 0 0.75rem;
    font-size: 0.9rem;
    max-width: min(420px, 100%);
}
.reply-label { font-weight: 600; margin: 0 0 0.25rem; color: #4a5568; font-size: 0.8rem; }
.report-footer {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.35rem;
}
.subject-link {
    color: #0db7ff;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}
.subject-link:hover { text-decoration: underline; }
.reported-user-card {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 0.85rem;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    max-width: 220px;
}
.reported-user-info { min-width: 0; }
.reported-user-label {
    margin: 0 0 0.15rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.reported-user-name {
    color: #0db7ff;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    word-break: break-word;
}
.reported-user-name:hover { text-decoration: underline; }
.btn-row {
    flex-shrink: 0;
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 0.85rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
}
.btn-row--danger {
    background: #fff;
    color: #c53030;
    border: 1px solid #fc8181;
}
.btn-row--danger:hover {
    background: #fff5f5;
}
.btn-delete {
    background: #e53e3e;
    color: #fff;
    border: none;
    padding: 0.65rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.empty { text-align: center; color: #718096; padding: 2rem; background: #f7fafc; border-radius: 8px; }
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
.modal-window {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 480px;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}
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
    flex: 1;
    padding: 0.65rem;
    background: #edf2f7;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.error { color: #c53030; font-size: 0.875rem; margin: 0 0 0.5rem; }
[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .filter-select { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .report-row { border-color: #333; }
[data-theme="dark"] .reply-block,
[data-theme="dark"] .reported-user-card { background: #1f1f1f; border-color: #333; }
[data-theme="dark"] .reply-label { color: #ccc; }
[data-theme="dark"] .expand-btn { color: #90cdf4; }
[data-theme="dark"] .btn-row--danger {
    background: #2a1515;
    border-color: #fc8181;
    color: #feb2b2;
}
[data-theme="dark"] .btn-row--danger:hover { background: #3d1f1f; }
[data-theme="dark"] .reporter-link,
[data-theme="dark"] .subject-link,
[data-theme="dark"] .reported-user-name { color: #90cdf4; }
[data-theme="dark"] .reply-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .btn-close { background: #2a2a2a; color: #f0f0f0; }
[data-theme="dark"] .empty { background: #141414; border: 1px solid #333; color: #aaa; }
[data-theme="dark"] .modal-window { background: #141414; border: 1px solid #333; color: #f0f0f0; }

@media (max-width: 768px) {
    .admin-panel { margin: 1rem auto; padding: 0 1rem 2rem; }
    .admin-top { flex-direction: column; align-items: flex-start; }
    .report-layout { flex-direction: column; }
    .reported-user-card { max-width: 100%; width: 100%; }
}
</style>
