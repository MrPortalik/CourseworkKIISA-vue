<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import BlockUserModal from '@/Components/Admin/BlockUserModal.vue'
import RoleAssignMenu from '@/Components/Admin/RoleAssignMenu.vue'
import { ref } from 'vue'

const props = defineProps({
    profileUser: Object,
    articlesCount: Number,
    messages: Array,
    canManageRoles: Boolean,
    canPromoteModerator: Boolean,
    canManageUser: Boolean,
})

const showBlockModal = ref(false)
const messageForm = useForm({ body: '' })

const sendMessage = () => {
    messageForm.post(route('admin.users.message', props.profileUser.id), {
        preserveScroll: true,
        onSuccess: () => messageForm.reset(),
    })
}

const promoteModerator = () => router.post(route('admin.users.promote-moderator', props.profileUser.id), {}, { preserveScroll: true })
const demote = () => router.post(route('admin.users.demote', props.profileUser.id), {}, { preserveScroll: true })
const demoteModerator = () => router.post(route('admin.users.demote-moderator', props.profileUser.id), {}, { preserveScroll: true })
const unblock = () => router.post(route('admin.users.unblock', props.profileUser.id), {}, { preserveScroll: true })

const formatBlockUntil = (value) => {
    if (!value) return ''
    return new Date(value).toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const destroy = () => {
    if (confirm('Удалить пользователя безвозвратно?')) {
        router.delete(route('admin.users.destroy', props.profileUser.id))
    }
}

const roleLabel = (role) => {
    if (role === 'owner') return 'Владелец'
    if (role === 'admin') return 'Администратор'
    if (role === 'moderator') return 'Модератор'
    return 'Пользователь'
}

const roleBadgeClass = (role) => {
    if (role === 'owner') return 'role-badge--owner'
    if (role === 'admin') return 'role-badge--admin'
    if (role === 'moderator') return 'role-badge--moderator'
    return 'role-badge--user'
}
</script>

<template>
    <PageHead :title="profileUser.name" description="Профиль пользователя в админ-панели КИИСА." />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <Link :href="route('admin.users.index')" class="back-link">← К списку пользователей</Link>

            <div class="profile-card">
                <UserAvatar :src="profileUser.avatar" :alt="profileUser.name" :size="72" />
                <div>
                    <h2 class="profile-name">{{ profileUser.name }}</h2>
                    <p class="meta">{{ profileUser.email }}</p>
                    <span class="role-badge" :class="roleBadgeClass(profileUser.role)">{{ roleLabel(profileUser.role) }}</span>
                    <template v-if="profileUser.is_blocked">
                        <p class="blocked">Заблокирован</p>
                        <p v-if="profileUser.block_reason" class="blocked-reason">{{ profileUser.block_reason }}</p>
                        <p v-if="!profileUser.blocked_until" class="blocked-meta">Перманентная блокировка</p>
                        <p v-else class="blocked-meta">До: {{ formatBlockUntil(profileUser.blocked_until) }}</p>
                    </template>
                    <p class="meta">Статей: {{ articlesCount }}</p>
                    <Link :href="route('authors.show', profileUser.id)" class="public-profile">Публичный профиль</Link>
                </div>
            </div>

            <div v-if="canManageUser || canManageRoles || canPromoteModerator" class="actions">
                <template v-if="canManageRoles">
                    <RoleAssignMenu :user-id="profileUser.id" :user-role="profileUser.role" />
                    <button v-if="profileUser.role === 'admin'" type="button" class="btn-action btn-action--warn" @click="demote">
                        Снять права администратора
                    </button>
                    <button v-if="profileUser.role === 'moderator'" type="button" class="btn-action btn-action--warn" @click="demoteModerator">
                        Снять права модератора
                    </button>
                    <button v-if="profileUser.role !== 'owner'" type="button" class="btn-action btn-action--danger" @click="destroy">
                        Удалить
                    </button>
                </template>
                <template v-else-if="canPromoteModerator">
                    <button v-if="profileUser.role === 'user'" type="button" class="btn-action btn-action--moderator" @click="promoteModerator">
                        Назначить модератором
                    </button>
                </template>
                <template v-if="canManageUser">
                    <button v-if="!profileUser.is_blocked" type="button" class="btn-action btn-action--danger" @click="showBlockModal = true">
                        Заблокировать
                    </button>
                    <button v-else type="button" class="btn-action" @click="unblock">
                        Разблокировать
                    </button>
                </template>
            </div>

            <form class="message-form" @submit.prevent="sendMessage">
                <label class="form-label">Написать пользователю</label>
                <textarea v-model="messageForm.body" rows="3" class="message-input" placeholder="Текст уведомления" required />
                <button type="submit" class="btn-add btn-accent" :disabled="messageForm.processing">Отправить</button>
            </form>

            <div v-if="messages.length" class="thread-section">
                <p class="form-label">Переписка</p>
                <article v-for="thread in messages" :key="thread.id" class="thread-card">
                    <p class="thread-meta">{{ thread.sender?.name }} → {{ thread.recipient?.name }}</p>
                    <p>{{ thread.body }}</p>
                    <div v-for="reply in thread.replies" :key="reply.id" class="reply">
                        <p class="thread-meta">{{ reply.sender?.name }}</p>
                        <p>{{ reply.body }}</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <BlockUserModal
        :open="showBlockModal"
        :user-id="profileUser.id"
        @close="showBlockModal = false"
    />
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
.back-link {
    display: inline-block;
    margin-bottom: 1rem;
    color: #0db7ff;
    font-weight: 600;
    text-decoration: none;
}
.back-link:hover { text-decoration: underline; }
.profile-card { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1rem; }
.profile-name { margin: 0 0 0.25rem; font-size: 1.35rem; }
.meta { color: #718096; margin: 0.15rem 0; font-size: 0.9rem; }
.role-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    margin: 0.25rem 0;
}
.role-badge--user { background: #e0f2fe; color: #0369a1; }
.role-badge--admin { background: #fee2e2; color: #b91c1c; }
.role-badge--owner { background: #ede9fe; color: #6d28d9; }
.role-badge--moderator { background: #dcfce7; color: #166534; }
.blocked { color: #c53030; font-weight: 700; margin: 0.15rem 0; }
.blocked-reason,
.blocked-meta {
    color: #c53030;
    margin: 0.15rem 0;
    font-size: 0.85rem;
    line-height: 1.4;
}
.public-profile { color: #0db7ff; font-weight: 600; font-size: 0.9rem; }
.actions { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 1rem; }
.btn-action {
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
}
.btn-action--moderator { background: #48bb78; }
.btn-action--warn { background: #f59e0b; }
.btn-action--danger { background: #e53e3e; }
.form-label { font-weight: 600; font-size: 0.9rem; margin: 0 0 0.5rem; display: block; }
.message-form { margin-bottom: 1rem; }
.message-input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    box-sizing: border-box;
    margin-bottom: 0.5rem;
}
.thread-section { margin-bottom: 1rem; }
.thread-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.65rem 0.85rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}
.thread-meta { font-size: 0.75rem; color: #718096; margin: 0 0 0.25rem; }
.reply { margin-top: 0.5rem; padding-left: 0.65rem; border-left: 3px solid #0db7ff; }
.btn-add {
    background: #4299e1;
    color: #fff;
    border: none;
    padding: 0.6rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .message-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .thread-card { border-color: #333; }
[data-theme="dark"] .back-link,
[data-theme="dark"] .public-profile { color: #90cdf4; }

@media (max-width: 768px) {
    .admin-panel { margin: 1rem auto; padding: 0 1rem 2rem; }
    .admin-top { flex-direction: column; align-items: flex-start; }
    .profile-card { flex-direction: column; }
}
</style>
