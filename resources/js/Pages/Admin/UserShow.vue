<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import { ref } from 'vue'

const props = defineProps({
    profileUser: Object,
    articlesCount: Number,
    messages: Array,
    canManageRoles: Boolean,
    canManageUser: Boolean,
})

const showModal = ref(true)
const messageForm = useForm({ body: '' })

const close = () => {
    showModal.value = false
    router.visit(route('admin.users.index'))
}

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

    <div v-if="showModal" class="modal-overlay">
        <div class="modal-window modal-window--wide" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h2>{{ profileUser.name }}</h2>
                <button type="button" class="close-x" aria-label="Закрыть" @click="close">×</button>
            </div>

            <div class="profile-card">
                <UserAvatar :src="profileUser.avatar" :alt="profileUser.name" :size="72" />
                <div>
                    <p class="meta">{{ profileUser.email }} · {{ roleLabel(profileUser.role) }}</p>
                    <p v-if="profileUser.is_blocked" class="blocked">Заблокирован</p>
                    <p class="meta">Статей: {{ articlesCount }}</p>
                    <Link :href="route('authors.show', profileUser.id)" class="public-profile">Публичный профиль</Link>
                </div>
            </div>

            <div v-if="canManageUser || canManageRoles" class="actions">
                <template v-if="canManageRoles">
                    <button v-if="profileUser.role === 'user'" type="button" class="btn-action" @click="promote">
                        Назначить администратором
                    </button>
                    <button v-if="profileUser.role === 'admin'" type="button" class="btn-action btn-action--warn" @click="demote">
                        Снять права администратора
                    </button>
                    <button v-if="profileUser.role !== 'owner'" type="button" class="btn-action btn-action--danger" @click="destroy">
                        Удалить
                    </button>
                </template>
                <template v-if="canManageUser">
                    <button v-if="!profileUser.is_blocked" type="button" class="btn-action btn-action--danger" @click="block">
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

            <button type="button" class="btn-close" @click="close">Закрыть</button>
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
.modal-window--wide { max-width: 600px; }
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
.profile-card { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.meta { color: #718096; margin: 0.15rem 0; font-size: 0.9rem; }
.blocked { color: #c53030; font-weight: 700; margin: 0.15rem 0; }
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
    width: 100%;
    background: #4299e1;
    color: #fff;
    border: none;
    padding: 0.6rem;
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
    margin-top: 0.5rem;
}
[data-theme="dark"] .modal-window { background: #141414; border: 1px solid #333; color: #f0f0f0; }
[data-theme="dark"] .message-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .thread-card { border-color: #333; }
[data-theme="dark"] .btn-close { background: #2a2a2a; color: #f0f0f0; }
</style>
