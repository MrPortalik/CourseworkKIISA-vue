<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'

const props = defineProps({
    profileUser: Object,
    articlesCount: Number,
    messages: Array,
    canManageRoles: Boolean,
    canManageUser: Boolean,
})

const messageForm = useForm({ body: '' })

const sendMessage = () => {
    messageForm.post(route('admin.users.message', props.profileUser.id), {
        preserveScroll: true,
        onSuccess: () => messageForm.reset(),
    })
}

const promote = () => router.post(route('admin.users.promote', props.profileUser.id), {}, { preserveScroll: true })
const demote = () => router.post(route('admin.users.demote', props.profileUser.id), {}, { preserveScroll: true })
const block = () => router.post(route('admin.users.block', props.profileUser.id), {}, { preserveScroll: true })
const unblock = () => router.post(route('admin.users.unblock', props.profileUser.id), {}, { preserveScroll: true })
const destroy = () => {
    if (confirm('Удалить пользователя безвозвратно?')) {
        router.delete(route('admin.users.destroy', props.profileUser.id))
    }
}

const roleLabel = (role) => {
    if (role === 'owner') return 'Владелец'
    if (role === 'admin') return 'Администратор'
    return 'Пользователь'
}
</script>

<template>
    <PageHead :title="profileUser.name" description="Профиль пользователя в админ-панели КИИСА." />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <Link :href="route('admin.users.index')" class="back-link">← К списку пользователей</Link>

        <div class="profile-card">
            <UserAvatar :src="profileUser.avatar" :alt="profileUser.name" :size="96" />
            <div>
                <h1>{{ profileUser.name }}</h1>
                <p class="meta">{{ profileUser.email }} · {{ roleLabel(profileUser.role) }}</p>
                <p v-if="profileUser.is_blocked" class="blocked">Заблокирован</p>
                <p class="meta">Статей: {{ articlesCount }}</p>
                <Link :href="route('authors.show', profileUser.id)" class="public-profile">Публичный профиль</Link>
            </div>
        </div>

        <div v-if="canManageUser || canManageRoles" class="actions">
            <template v-if="canManageRoles">
                <button
                    v-if="profileUser.role === 'user'"
                    type="button"
                    class="btn"
                    @click="promote"
                >
                    Назначить администратором
                </button>
                <button
                    v-if="profileUser.role === 'admin'"
                    type="button"
                    class="btn btn--warn"
                    @click="demote"
                >
                    Снять права администратора
                </button>
                <button
                    v-if="profileUser.role !== 'owner'"
                    type="button"
                    class="btn btn--danger"
                    @click="destroy"
                >
                    Удалить пользователя
                </button>
            </template>
            <template v-if="canManageUser">
                <button
                    v-if="!profileUser.is_blocked"
                    type="button"
                    class="btn btn--danger"
                    @click="block"
                >
                    Заблокировать доступ
                </button>
                <button
                    v-else
                    type="button"
                    class="btn"
                    @click="unblock"
                >
                    Разблокировать
                </button>
            </template>
        </div>

        <div class="message-section">
            <h2>Написать пользователю</h2>
            <form class="message-form" @submit.prevent="sendMessage">
                <textarea v-model="messageForm.body" rows="4" class="message-input" placeholder="Текст уведомления" required />
                <button type="submit" class="btn" :disabled="messageForm.processing">Отправить</button>
            </form>
        </div>

        <div v-if="messages.length" class="thread-section">
            <h2>Переписка</h2>
            <article v-for="thread in messages" :key="thread.id" class="thread-card">
                <p class="thread-meta">{{ thread.sender?.name }} → {{ thread.recipient?.name }}</p>
                <p>{{ thread.body }}</p>
                <div v-for="reply in thread.replies" :key="reply.id" class="reply">
                    <p class="thread-meta">{{ reply.sender?.name }}</p>
                    <p>{{ reply.body }}</p>
                </div>
            </article>
        </div>
    </section>
</template>

<style scoped>
.admin-panel { max-width: 800px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
.back-link { display: inline-block; margin-bottom: 1rem; color: #0db7ff; text-decoration: none; font-weight: 600; }
.profile-card { display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; }
.meta { color: #718096; margin: 0.25rem 0; }
.blocked { color: #c53030; font-weight: 700; }
.public-profile { color: #0db7ff; font-weight: 600; }
.actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem; }
.btn { background: #0db7ff; color: #fff; border: none; border-radius: 8px; padding: 0.55rem 1rem; font-weight: 600; cursor: pointer; }
.btn--warn { background: #f59e0b; }
.btn--danger { background: #e53e3e; }
.message-section, .thread-section { margin-top: 1.5rem; }
.message-form { display: grid; gap: 0.75rem; }
.message-input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 8px; font: inherit; }
.thread-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 0.75rem; }
.thread-meta { font-size: 0.8rem; color: #718096; margin: 0 0 0.35rem; }
.reply { margin-top: 0.65rem; padding-left: 0.75rem; border-left: 3px solid #0db7ff; }
[data-theme="dark"] .message-input { background: #141414; color: #f0f0f0; border-color: #404040; }
[data-theme="dark"] .thread-card { border-color: #333; }
</style>
