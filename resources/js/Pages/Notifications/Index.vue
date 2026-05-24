<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import UserAvatar from '@/Components/UserAvatar.vue'

const props = defineProps({
    notifications: Object,
    authors: Array,
})

const tab = ref('notifications')

const markAllRead = () => {
    router.post(route('notifications.read-all'))
}

const markRead = (id) => {
    router.post(route('notifications.read', id))
}
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
                    class="tab-btn action-button"
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
                    Прочитать все
                </button>
            </div>

            <ul v-if="notifications.data.length" class="notifications-list">
                <li
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    class="notification-item"
                    :class="{ unread: !notification.read_at }"
                >
                    <p>{{ notification.data.message }}</p>
                    <div class="notification-actions">
                        <Link
                            v-if="notification.data.article_slug"
                            :href="route('articles.show', notification.data.article_slug)"
                            class="link"
                        >
                            Открыть статью
                        </Link>
                        <Link
                            v-else-if="notification.data.author_id"
                            :href="route('authors.show', notification.data.author_id)"
                            class="link"
                        >
                            Открыть профиль
                        </Link>
                        <button v-if="!notification.read_at" class="read-btn" @click="markRead(notification.id)">
                            Прочитано
                        </button>
                    </div>
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

.mark-all {
    background: #edf2f7;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
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

[data-theme="dark"] .mark-all {
    background: #141414;
    color: #f0f0f0;
    border: 1px solid #404040;
}

[data-theme="dark"] .meta,
[data-theme="dark"] .empty {
    color: #aaa;
}
</style>
