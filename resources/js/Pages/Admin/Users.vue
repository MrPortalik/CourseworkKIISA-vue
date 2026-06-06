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

const q = ref(props.filters?.q || '')

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

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Пользователи</h1>
            <nav class="admin-nav">
                <Link :href="route('admin.index')" class="admin-tab">Модерация</Link>
                <Link :href="route('admin.reports.index')" class="admin-tab">Жалобы</Link>
                <Link :href="route('admin.categories')" class="admin-tab">Категории</Link>
                <Link :href="route('admin.taxonomy')" class="admin-tab">Теги</Link>
            </nav>
        </div>

        <form class="search-form" @submit.prevent="search">
            <input v-model="q" type="search" class="search-input" placeholder="Имя, email или ID" />
            <button type="submit" class="btn">Найти</button>
        </form>

        <div class="users-table">
            <div v-for="user in users.data" :key="user.id" class="user-row">
                <div>
                    <strong>{{ user.name }}</strong>
                    <p class="meta">{{ user.email }} · id_{{ user.id }}</p>
                </div>
                <div class="badges">
                    <span class="role-badge">{{ roleLabel(user.role) }}</span>
                    <span v-if="user.is_blocked" class="blocked-badge">Заблокирован</span>
                </div>
                <Link :href="route('admin.users.show', user.id)" class="profile-btn">Профиль</Link>
            </div>
        </div>

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
    </section>
</template>

<style scoped>
.admin-panel { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
.admin-top { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
.admin-nav { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.admin-tab { padding: 0.5rem 1rem; border-radius: 8px; background: #edf2f7; text-decoration: none; color: #2d3748; font-weight: 600; }
.search-form { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
.search-input { flex: 1; padding: 0.65rem 0.85rem; border: 1px solid #cbd5e0; border-radius: 8px; }
.users-table { display: grid; gap: 0.75rem; }
.user-row { display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: center; padding: 0.85rem 1rem; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
.meta { margin: 0.2rem 0 0; color: #718096; font-size: 0.875rem; }
.badges { display: flex; gap: 0.35rem; flex-wrap: wrap; }
.role-badge, .blocked-badge { font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 999px; }
.role-badge { background: #e0f2fe; color: #0369a1; }
.blocked-badge { background: #fee2e2; color: #b91c1c; }
.profile-btn, .btn { background: #0db7ff; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; }
.pagination { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.5rem; }
.page-link { padding: 0.5rem 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; text-decoration: none; color: #4a5568; }
.page-link.active { background: #4299e1; color: #fff; }
@media (max-width: 640px) {
    .user-row { grid-template-columns: 1fr; }
}
[data-theme="dark"] .user-row { background: #141414; border-color: #333; }
[data-theme="dark"] .admin-tab { background: #2a2a2a; color: #f0f0f0; }
</style>
