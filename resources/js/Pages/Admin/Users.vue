<script setup>
import { Link, router } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { ref } from 'vue'

const props = defineProps({
    users: Object,
    filters: Object,
    canManageRoles: Boolean,
})

const showModal = ref(true)
const q = ref(props.filters?.q || '')

const close = () => {
    showModal.value = false
    router.visit(route('admin.index'))
}

const search = () => {
    router.get(route('admin.users.index'), { q: q.value || undefined }, { preserveState: true })
}

const roleLabel = (role) => {
    if (role === 'owner') return 'Владелец'
    if (role === 'admin') return 'Администратор'
    return 'Пользователь'
}
</script>

<template>
    <PageHead title="Пользователи" description="Управление пользователями портала КИИСА." />
    <HeaderComponent />

    <div v-if="showModal" class="modal-overlay">
        <div class="modal-window modal-window--wide" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h2>Пользователи</h2>
                <button type="button" class="close-x" aria-label="Закрыть" @click="close">×</button>
            </div>

            <form class="search-form" @submit.prevent="search">
                <input v-model="q" type="search" class="search-input" placeholder="Имя, email или ID" />
                <button type="submit" class="btn-add btn-accent">Найти</button>
            </form>

            <ul class="user-list">
                <li v-for="user in users.data" :key="user.id" class="user-row">
                    <div class="user-info">
                        <strong>{{ user.name }}</strong>
                        <span class="meta">{{ user.email }} · id_{{ user.id }}</span>
                    </div>
                    <div class="badges">
                        <span class="role-badge">{{ roleLabel(user.role) }}</span>
                        <span v-if="user.is_blocked" class="blocked-badge">Заблокирован</span>
                    </div>
                    <Link :href="route('admin.users.show', user.id)" class="profile-btn">Профиль</Link>
                </li>
            </ul>

            <nav v-if="users.links?.length > 3" class="pagination">
                <Link
                    v-for="(link, index) in users.links"
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
.modal-window--wide { max-width: 640px; }
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
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
.search-form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.search-input {
    flex: 1;
    padding: 0.55rem 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 0.95rem;
}
.user-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
}
.user-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 0.75rem;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #edf2f7;
}
.user-row:last-child { border-bottom: none; }
.user-info { display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; }
.meta { color: #718096; font-size: 0.8rem; }
.badges { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.role-badge, .blocked-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
}
.role-badge { background: #e0f2fe; color: #0369a1; }
.blocked-badge { background: #fee2e2; color: #b91c1c; }
.profile-btn {
    background: #0db7ff;
    color: #fff;
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    white-space: nowrap;
}
.btn-add {
    background: #4299e1;
    color: #fff;
    border: none;
    padding: 0.55rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    white-space: nowrap;
}
.pagination { display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 1rem; }
.page-link { padding: 0.4rem 0.7rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; text-decoration: none; color: #4a5568; font-size: 0.85rem; }
.page-link.active { background: #4299e1; color: #fff; }
.btn-close {
    width: 100%;
    padding: 0.65rem;
    background: #edf2f7;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
@media (max-width: 520px) {
    .user-row { grid-template-columns: 1fr; }
}
[data-theme="dark"] .modal-window { background: #141414; border: 1px solid #333; color: #f0f0f0; }
[data-theme="dark"] .search-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .user-row { border-color: #333; }
[data-theme="dark"] .btn-close { background: #2a2a2a; color: #f0f0f0; }
</style>
