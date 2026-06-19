<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    userId: { type: Number, required: true },
    userRole: { type: String, required: true },
})

const open = ref(false)
const root = ref(null)

const roles = [
    { key: 'admin', label: 'Администратор', route: 'admin.users.promote' },
    { key: 'moderator', label: 'Модератор', route: 'admin.users.promote-moderator' },
]

const assignRole = (role) => {
    open.value = false
    router.post(route(role.route, props.userId), {}, { preserveScroll: true })
}

const onDocumentClick = (event) => {
    if (!open.value || !root.value) return
    if (!root.value.contains(event.target)) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
    <div v-if="userRole === 'user'" ref="root" class="role-menu">
        <button type="button" class="role-menu__trigger" @click.stop="open = !open">
            Назначить роль
            <span class="role-menu__chevron" :class="{ 'role-menu__chevron--open': open }">▾</span>
        </button>
        <ul v-if="open" class="role-menu__list">
            <li v-for="role in roles" :key="role.key">
                <button type="button" class="role-menu__item" :class="`role-menu__item--${role.key}`" @click="assignRole(role)">
                    {{ role.label }}
                </button>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.role-menu {
    position: relative;
    display: inline-block;
}

.role-menu__trigger {
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.role-menu__chevron {
    font-size: 0.75rem;
    transition: transform 0.15s;
}

.role-menu__chevron--open {
    transform: rotate(180deg);
}

.role-menu__list {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 30;
    min-width: 190px;
    margin: 0;
    padding: 0.35rem;
    list-style: none;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
}

.role-menu__item {
    width: 100%;
    text-align: left;
    border: none;
    background: transparent;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}

.role-menu__item:hover {
    background: #f7fafc;
}

.role-menu__item--admin {
    color: #b91c1c;
}

.role-menu__item--moderator {
    color: #166534;
}

[data-theme="dark"] .role-menu__trigger {
    background: transparent;
    color: #d4af37;
    border: 2px solid #d4af37;
}

[data-theme="dark"] .role-menu__trigger:hover {
    background: rgba(212, 175, 55, 0.1);
}

[data-theme="dark"] .role-menu__list {
    background: #1a1a1a;
    border-color: #404040;
}

[data-theme="dark"] .role-menu__item:hover {
    background: #262626;
}
</style>
