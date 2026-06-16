<script setup>
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const pendingReportsCount = computed(() => page.props.pendingReportsCount ?? 0)
const pendingModerationCount = computed(() => page.props.pendingModerationCount ?? 0)
const isFullStaff = computed(() => ['admin', 'owner'].includes(page.props.auth?.user?.role))

const isActive = (patterns) => {
    const current = route().current()
    if (!current) return false
    return patterns.some((p) => current === p || current.startsWith(p))
}
</script>

<template>
    <nav class="admin-nav">
        <Link
            :href="route('admin.index')"
            class="admin-tab btn-accent"
            :class="{ 'admin-tab--active': isActive(['admin.index']) }"
        >
            Модерация статей
            <span v-if="pendingModerationCount > 0" class="admin-badge">{{ pendingModerationCount }}</span>
        </Link>
        <template v-if="isFullStaff">
            <Link
                :href="route('admin.reports.index')"
                class="admin-tab btn-accent"
                :class="{ 'admin-tab--active': isActive(['admin.reports']) }"
            >
                Жалобы и предложения
                <span v-if="pendingReportsCount > 0" class="admin-badge">{{ pendingReportsCount }}</span>
            </Link>
            <Link
                :href="route('admin.users.index')"
                class="admin-tab btn-accent"
                :class="{ 'admin-tab--active': isActive(['admin.users']) }"
            >
                Пользователи
            </Link>
            <Link
                :href="route('admin.categories')"
                class="admin-tab btn-accent"
                :class="{ 'admin-tab--active': isActive(['admin.categories']) }"
            >
                Категории
            </Link>
            <Link
                :href="route('admin.taxonomy')"
                class="admin-tab btn-accent"
                :class="{ 'admin-tab--active': isActive(['admin.taxonomy', 'admin.tags']) }"
            >
                Теги
            </Link>
            <Link
                :href="route('admin.moderator-settings')"
                class="admin-tab admin-tab--settings btn-accent"
                :class="{ 'admin-tab--active': isActive(['admin.moderator-settings']) }"
            >
                Настройки модерации
            </Link>
        </template>
    </nav>
</template>

<style scoped>
.admin-nav {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.admin-tab--settings {
    margin-left: auto;
}
.admin-tab {
    text-decoration: none;
    font-weight: 600;
    color: #4a5568;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.admin-tab--active {
    box-shadow: 0 0 0 2px #0db7ff;
}
.admin-badge {
    background: #e53e3e;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    min-width: 1.25rem;
    text-align: center;
    line-height: 1.3;
}
[data-theme="dark"] .admin-tab.btn-accent {
    color: #0a0a0a;
}
</style>
