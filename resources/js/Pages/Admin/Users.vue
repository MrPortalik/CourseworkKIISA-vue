<script setup>
import { Link, router } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import PaginationNav from '@/Components/UI/PaginationNav.vue'
import { ref } from 'vue'

const props = defineProps({
    users: Object,
    filters: Object,
    canManageRoles: Boolean,
})

const q = ref(props.filters?.q || '')

const search = () => {
    router.get(route('admin.users.index'), { q: q.value || undefined }, { preserveState: true })
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
    <PageHead title="Пользователи" description="Управление пользователями портала КИИСА." />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <h2>Пользователи</h2>

            <form class="search-form" @submit.prevent="search">
                <input v-model="q" type="search" class="search-input" placeholder="Имя, email или ID" />
                <button type="submit" class="btn-add btn-accent">Найти</button>
            </form>

            <ul class="user-list">
                <li v-for="user in users.data" :key="user.id" class="user-row">
                    <div class="user-info">
                        <UserAvatar :src="user.avatar" :alt="user.name" :size="40" />
                        <div class="user-text">
                            <strong>{{ user.name }}</strong>
                            <span class="meta">{{ user.email }} · id_{{ user.id }}</span>
                        </div>
                    </div>
                    <div class="badges">
                        <span class="role-badge" :class="roleBadgeClass(user.role)">{{ roleLabel(user.role) }}</span>
                        <span v-if="user.is_blocked" class="blocked-badge">Заблокирован</span>
                    </div>
                    <Link :href="route('admin.users.show', user.id)" class="profile-btn">Профиль</Link>
                </li>
            </ul>

            <PaginationNav :links="users.links" />
        </div>
    </section>
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
.section h2 { margin: 0 0 0.75rem; }
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
.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}
.user-text {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}
.meta { color: #718096; font-size: 0.8rem; }
.badges { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.role-badge, .blocked-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
}
.role-badge--user { background: #e0f2fe; color: #0369a1; }
.role-badge--admin { background: #fee2e2; color: #b91c1c; }
.role-badge--owner { background: #ede9fe; color: #6d28d9; }
.role-badge--moderator { background: #dcfce7; color: #166534; }
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
@media (max-width: 520px) {
    .user-row { grid-template-columns: 1fr; }
}
[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .search-input { background: #1a1a1a; border-color: #404040; color: #f0f0f0; }
[data-theme="dark"] .user-row { border-color: #333; }

@media (max-width: 768px) {
    .admin-panel { margin: 1rem auto; padding: 0 1rem 2rem; }
    .admin-top { flex-direction: column; align-items: flex-start; }
}
</style>
