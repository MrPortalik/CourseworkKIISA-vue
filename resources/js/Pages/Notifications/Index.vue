<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'

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

const openArticle = (notification) => {
    if (!notification.read_at) {
        markRead(notification.id)
    }
}

const respondCoauthor = (notification, action) => {
    const coauthorId = notification.data.coauthor_id
    if (!coauthorId) return
    router.post(route(`coauthors.${action}`, coauthorId), {}, {
        ...inertiaOpts,
        onSuccess: () => {
            if (!notification.read_at) {
                markRead(notification.id)
            }
        },
    })
}

const isCoauthorInvite = (n) => n.data?.type === 'coauthor_invitation'
</script>

<template>
    <Head title="Уведомления" />
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
                    :class="{ unread: !notification.read_at }"
                >
                    <template v-if="isCoauthorInvite(notification)">
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
                                @click="respondCoauthor(notification, 'accept')"
                            >
                                Да
                            </button>
                            <button
                                type="button"
                                class="decline-btn"
                                @click="respondCoauthor(notification, 'decline')"
                            >
                                Нет
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
                            <button v-if="!notification.read_at" class="read-btn" @click="markRead(notification.id)">
                                Прочитано
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <p class="notification-text">{{ notification.data.message }}</p>
                        <div class="notification-actions">
                            <Link
                                v-if="notification.data.author_id"
                                :href="route('authors.show', notification.data.author_id)"
                                class="link"
                            >
                                Открыть профиль
                            </Link>
                            <button v-if="!notification.read_at" class="read-btn" @click="markRead(notification.id)">
                                Прочитано
                            </button>
                        </div>
                    </template>
                </li>
            </ul>
            <p v-else class="empty">Уведомлений пока нет</p>

            <nav v-if="notifications.links?.length > 3" class="pagination">
                <Link
                    v-for="(link, index) in notifications.links"
                    :key="index"
                    :href="link.url || '#'"
                    class="page-link"
                    :class="{ active: link.active, disabled: !link.url }"
                    v-html="link.label"
                />
            </nav>
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
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    background: white;
}

.notification-item.unread {
    border-left: 4px solid #4299e1;
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

.link {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.read-btn,
.accept-btn,
.decline-btn {
    border: none;
    padding: 0.4rem 0.85rem;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
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

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    text-decoration: none;
    color: #4a5568;
}

.page-link.active {
    background: #4299e1;
    color: white;
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

[data-theme="dark"] .notification-link:hover {
    color: #90cdf4;
}

[data-theme="dark"] .mark-all,
[data-theme="dark"] .read-btn {
    background: #141414;
    color: #f0f0f0;
    border: 1px solid #404040;
}

[data-theme="dark"] .meta,
[data-theme="dark"] .empty {
    color: #aaa;
}

[data-theme="dark"] .profile-link {
    border-radius: 20px;
    font-weight: 600;
    padding: 0.5rem 1.25rem;
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
