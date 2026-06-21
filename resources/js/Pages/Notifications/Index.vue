<script setup>
import { Link, router } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import { ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import PaginationNav from '@/Components/UI/PaginationNav.vue'

const props = defineProps({
    notifications: Object,
    authors: Array,
})

const tab = ref('notifications')
const inertiaOpts = { preserveScroll: true }

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, inertiaOpts)
}

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, inertiaOpts)
}

const deleteNotification = (id) => {
    router.delete(route('notifications.destroy', id), inertiaOpts)
}

const openArticle = (notification) => {
    if (!notification.read_at) {
        markRead(notification.id)
    }
}

const respondCoauthor = (notification, action) => {
    const coauthorId = notification.data.coauthor_id
    if (!coauthorId || hasCoauthorAction(notification)) return
    router.post(route(`coauthors.${action}`, coauthorId), {}, inertiaOpts)
}

const isCoauthorInvite = (n) => n.data?.type === 'coauthor_invitation'
const isAdminMessage = (n) => n.data?.type === 'admin_message'
const isReportResponse = (n) => n.data?.type === 'report_response'
const isAccountBlocked = (n) => n.data?.type === 'account_blocked'

const isArticleUnavailable = (n) => (
    !!n.data?.article_slug && n.data?.article_available === false
)

const articleUnavailableMessage = 'Статья, к которой относилось это уведомление, была удалена или аккаунт автора был заблокирован.'

const formatBlockUntil = (iso) => {
    if (!iso) return ''
    return new Date(iso).toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const hasCoauthorAction = (n) => ['accepted', 'declined'].includes(n.data?.user_action)
const hasReadAction = (n) => !!n.data?.user_action || !!n.read_at
const hasReplyAction = (n) => n.data?.user_action === 'replied'

const actionLabel = (n) => n.action_label || null

const replyBodies = ref({})
const replyProcessing = ref({})

const submitReply = (notification) => {
    if (hasReplyAction(notification)) return
    const messageId = notification.data.message_id
    const body = replyBodies.value[notification.id]?.trim()
    if (!messageId || !body) return

    replyProcessing.value[notification.id] = true
    router.post(route('messages.reply', messageId), { body }, {
        preserveScroll: true,
        onFinish: () => {
            replyProcessing.value[notification.id] = false
        },
    })
}
</script>

<template>
    <PageHead
        title="Уведомления"
        description="Уведомления о публикациях, подписках и событиях на портале КИИСА."
    />
    <HeaderComponent />

    <section class="notifications-page content-area">
        <div class="page-header">
            <h1>Уведомления</h1>
            <nav class="tabs">
                <button
                    type="button"
                    class="tab-btn"
                    :class="{ active: tab === 'notifications' }"
                    @click="tab = 'notifications'"
                >
                    Лента
                </button>
                <button
                    type="button"
                    class="tab-btn"
                    :class="{ active: tab === 'subscriptions' }"
                    @click="tab = 'subscriptions'"
                >
                    Подписки
                </button>
            </nav>
        </div>

        <template v-if="tab === 'notifications'">
            <div class="toolbar">
                <button v-if="notifications.data.length" class="mark-all" @click="markAllRead">
                    Прочитать&nbsp;все
                </button>
            </div>

            <ul v-if="notifications.data.length" class="notifications-list">
                <li
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    class="notification-item"
                    :class="{
                        unread: !notification.read_at,
                        'notification-item--article-unavailable': isArticleUnavailable(notification),
                    }"
                >
                    <button
                        type="button"
                        class="delete-btn"
                        aria-label="Удалить уведомление"
                        @click="deleteNotification(notification.id)"
                    >
                        ×
                    </button>

                    <template v-if="isArticleUnavailable(notification)">
                        <p class="notification-unavailable-text">{{ articleUnavailableMessage }}</p>
                        <div class="notification-actions">
                            <button
                                type="button"
                                class="read-btn"
                                :disabled="hasReadAction(notification)"
                                @click="markRead(notification.id)"
                            >
                                Прочитано
                            </button>
                            <span v-if="actionLabel(notification)" class="action-status">
                                {{ actionLabel(notification) }}
                            </span>
                        </div>
                    </template>

                    <template v-else-if="isCoauthorInvite(notification)">
                        <p class="notification-text">{{ notification.data.message }}</p>
                        <div class="notification-actions">
                            <Link
                                v-if="notification.data.article_slug"
                                :href="route('articles.show', notification.data.article_slug)"
                                class="link"
                                @click="openArticle(notification)"
                            >
                                Открыть статью
                            </Link>
                            <button
                                type="button"
                                class="accept-btn"
                                :disabled="hasCoauthorAction(notification)"
                                @click="respondCoauthor(notification, 'accept')"
                            >
                                Да
                            </button>
                            <button
                                type="button"
                                class="decline-btn"
                                :disabled="hasCoauthorAction(notification)"
                                @click="respondCoauthor(notification, 'decline')"
                            >
                                Нет
                            </button>
                            <span v-if="actionLabel(notification)" class="action-status">
                                {{ actionLabel(notification) }}
                            </span>
                        </div>
                    </template>

                    <template v-else-if="isAdminMessage(notification)">
                        <p class="notification-text">
                            <strong>{{ notification.data.sender_name }}:</strong>
                            {{ notification.data.message }}
                        </p>
                        <form
                            v-if="!hasReplyAction(notification)"
                            class="reply-form"
                            @submit.prevent="submitReply(notification)"
                        >
                            <textarea
                                v-model="replyBodies[notification.id]"
                                rows="3"
                                class="reply-input"
                                placeholder="Ваш ответ"
                                required
                            />
                            <button type="submit" class="accept-btn" :disabled="replyProcessing[notification.id]">
                                Ответить
                            </button>
                        </form>
                        <p v-else class="action-status action-status--solo">
                            {{ actionLabel(notification) }}
                        </p>
                    </template>

                    <template v-else-if="isReportResponse(notification)">
                        <p class="notification-text">
                            <strong>Ответ администрации ({{ notification.data.admin_name }}):</strong>
                            {{ notification.data.message }}
                        </p>
                        <div class="notification-actions">
                            <Link
                                v-if="notification.data.article_slug"
                                :href="route('articles.show', notification.data.article_slug)"
                                class="link"
                                @click="openArticle(notification)"
                            >
                                Открыть статью
                            </Link>
                            <button
                                type="button"
                                class="read-btn"
                                :disabled="hasReadAction(notification)"
                                @click="markRead(notification.id)"
                            >
                                Прочитано
                            </button>
                            <span v-if="actionLabel(notification)" class="action-status">
                                {{ actionLabel(notification) }}
                            </span>
                        </div>
                    </template>

                    <template v-else-if="isAccountBlocked(notification)">
                        <p class="notification-text notification-text--warning">
                            {{ notification.data.message }}
                        </p>
                        <p v-if="notification.data.reason" class="block-reason">
                            Причина: {{ notification.data.reason }}
                        </p>
                        <p v-if="notification.data.permanent" class="block-until">Перманентная блокировка</p>
                        <p v-else-if="notification.data.blocked_until" class="block-until">
                            Разблокировка: {{ formatBlockUntil(notification.data.blocked_until) }}
                        </p>
                        <div class="notification-actions">
                            <button
                                type="button"
                                class="read-btn"
                                :disabled="hasReadAction(notification)"
                                @click="markRead(notification.id)"
                            >
                                Прочитано
                            </button>
                        </div>
                    </template>

                    <template v-else-if="notification.data.article_slug">
                        <div class="notification-body">
                            <Link
                                :href="route('articles.show', notification.data.article_slug)"
                                class="notification-link"
                                @click="openArticle(notification)"
                            >
                                {{ notification.data.message }}
                            </Link>
                            <p
                                v-if="notification.data.type === 'publication_rejected' && notification.data.reason"
                                class="rejection-reason"
                            >
                                {{ notification.data.reason }}
                            </p>
                        </div>
                        <div class="notification-actions">
                            <button
                                type="button"
                                class="read-btn"
                                :disabled="hasReadAction(notification)"
                                @click="markRead(notification.id)"
                            >
                                Прочитано
                            </button>
                            <span v-if="actionLabel(notification)" class="action-status">
                                {{ actionLabel(notification) }}
                            </span>
                        </div>
                    </template>

                    <template v-else>
                        <p class="notification-text">{{ notification.data.message }}</p>
                        <div class="notification-actions">
                            <Link
                                v-if="notification.data.author_id && notification.data.author_available !== false"
                                :href="route('authors.show', notification.data.author_id)"
                                class="link"
                            >
                                Открыть профиль
                            </Link>
                            <span
                                v-else-if="notification.data.author_id && notification.data.author_available === false"
                                class="unavailable-link"
                            >
                                Профиль недоступен
                            </span>
                            <button
                                type="button"
                                class="read-btn"
                                :disabled="hasReadAction(notification)"
                                @click="markRead(notification.id)"
                            >
                                Прочитано
                            </button>
                            <span v-if="actionLabel(notification)" class="action-status">
                                {{ actionLabel(notification) }}
                            </span>
                        </div>
                    </template>
                </li>
            </ul>
            <p v-else class="empty">Уведомлений пока нет</p>

            <PaginationNav :links="notifications.links" />
        </template>

        <template v-else>
            <ul v-if="authors.length" class="authors-list">
                <li v-for="author in authors" :key="author.id" class="author-item">
                    <UserAvatar :src="author.avatar" :alt="author.name" :size="48" class="author-avatar" />
                    <div>
                        <h3>{{ author.name }}</h3>
                        <p class="meta">Опубликовано: {{ author.published_articles_count }}</p>
                    </div>
                    <Link :href="route('authors.show', author.id)" class="profile-link">Профиль</Link>
                </li>
            </ul>
            <p v-else class="empty">Вы пока ни на кого не подписаны</p>
        </template>
    </section>
</template>

<style scoped>
.notifications-page {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1.5rem 3rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.tabs {
    display: flex;
    gap: 0.75rem;
}

.tab-btn {
    background: #edf2f7;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 0.5rem;
    cursor: pointer;
    font-weight: 600;
    color: #4a5568;
}

.tab-btn.active {
    background-color: var(--theme_black);
    color: white;
}

.toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1.25rem;
}

.mark-all {
    background: #edf2f7;
    border: none;
    padding: 0.65rem 1.35rem;
    margin-right: 0.25rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.notifications-list,
.authors-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notification-item,
.author-item {
    position: relative;
    padding: 1rem 2.25rem 1rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    background: white;
}

.notification-item.unread {
    border-left: 4px solid #4299e1;
}

.notification-item--article-unavailable {
    border: 2px solid #fc8181;
    background: #fff5f5;
}

.notification-item--article-unavailable.unread {
    border-left-width: 4px;
    border-color: #fc8181;
    border-left-color: #e53e3e;
}

.notification-unavailable-text {
    margin: 0 0 0.75rem;
    font-size: 0.95rem;
    line-height: 1.5;
    font-weight: 600;
    color: #c53030;
}

.delete-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #e53e3e;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.delete-btn:hover {
    background: #fee2e2;
}

.notification-link {
    display: block;
    color: inherit;
    text-decoration: none;
    font-size: 1rem;
    line-height: 1.45;
    margin-bottom: 0.5rem;
}

.notification-link:hover {
    color: #3182ce;
}

.notification-body {
    flex: 1;
    min-width: 0;
}

.notification-text--warning {
    color: #c53030;
    font-weight: 600;
}

.block-reason,
.block-until {
    margin: 0.35rem 0 0;
    font-size: 0.9rem;
    color: #718096;
    line-height: 1.45;
}

.rejection-reason {
    margin: 0.35rem 0 0;
    font-size: 0.9rem;
    color: #718096;
    line-height: 1.4;
}

.notification-text {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    line-height: 1.45;
}

.notification-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    align-items: center;
}

.action-status {
    font-size: 0.875rem;
    font-weight: 600;
    color: #718096;
    font-style: italic;
}

.action-status--solo {
    margin: 0.5rem 0 0;
}

.link {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.unavailable-link,
.unavailable-note {
    color: #a0aec0;
    font-weight: 600;
    font-size: 0.875rem;
}
.unavailable-note {
    display: block;
    margin-top: 0.25rem;
    font-weight: 500;
}

.reply-form {
    display: grid;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.reply-input {
    width: 100%;
    padding: 0.65rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    resize: vertical;
}

.accept-btn,
.decline-btn,
.read-btn {
    border: none;
    padding: 0.4rem 0.85rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
}

.accept-btn:disabled,
.decline-btn:disabled,
.read-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.read-btn {
    background: #edf2f7;
    color: #4a5568;
}

.accept-btn {
    background: #48bb78;
    color: #fff;
}

.decline-btn {
    background: #fed7d7;
    color: #c53030;
}

.author-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.meta {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.profile-link {
    margin-left: auto;
    background: #4299e1;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
}

.empty {
    text-align: center;
    color: #718096;
    padding: 3rem;
}

[data-theme="dark"] .notifications-page {
    color: #f0f0f0;
}

[data-theme="dark"] .tab-btn {
    background: #141414;
    color: #ccc;
}

[data-theme="dark"] .tab-btn.active {
    background: transparent;
    color: #ffffff;
    box-shadow: 0 0 0 2px #ffffff;
}

[data-theme="dark"] .notification-item,
[data-theme="dark"] .author-item {
    background: var(--theme_black);
    border-color: #333;
    color: #f0f0f0;
}

[data-theme="dark"] .notification-item--article-unavailable {
    background: #2a1515;
    border-color: #fc8181;
}

[data-theme="dark"] .notification-item--article-unavailable.unread {
    border-left-color: #fc8181;
}

[data-theme="dark"] .notification-unavailable-text {
    color: #feb2b2;
}

[data-theme="dark"] .notification-link:hover {
    color: #90cdf4;
}

[data-theme="dark"] .mark-all,
[data-theme="dark"] .read-btn {
    background: #141414;
    color: #f0f0f0;
    border: 1px solid #404040;
}

[data-theme="dark"] .reply-input {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}

[data-theme="dark"] .action-status {
    color: #aaa;
}

[data-theme="dark"] .delete-btn:hover {
    background: #3a1515;
}

[data-theme="dark"] .meta,
[data-theme="dark"] .empty {
    color: #aaa;
}

[data-theme="dark"] .profile-link {
    background: #ffffff;
    color: #0a0a0a;
    border: 2px solid #ffffff;
    border-radius: 20px;
    font-weight: 600;
    padding: 0.5rem 1.25rem;
}
[data-theme="dark"] .profile-link:hover {
    background: #f0f0f0;
    border-color: #f0f0f0;
    color: #0a0a0a;
}

@media (max-width: 768px) {
    .notifications-page {
        margin: 1rem auto;
        padding: 0 1rem 2rem;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .tabs {
        width: 100%;
    }

    .tab-btn {
        flex: 1;
        text-align: center;
    }

    .author-item {
        flex-wrap: wrap;
    }

    .profile-link {
        margin-left: 0;
        width: 100%;
        text-align: center;
    }
}
</style>
