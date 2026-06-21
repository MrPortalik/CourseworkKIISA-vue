<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'
import ModalPanel from '@/Components/ModalPanel.vue'

defineProps({
    tags: Array,
})

const ROLE_OPTIONS = [
    { value: '', label: 'Все авторы', hint: 'Любой пользователь может назначать тег при редактировании статьи.' },
    { value: 'moderator', label: 'Модератор и выше', hint: 'Только модераторы и администрация.' },
    { value: 'admin', label: 'Только администратор', hint: 'Только администраторы и владелец портала.' },
]

const newTag = useForm({ name: '', description: '', assign_role: '' })

const settingsOpen = ref(false)
const settingsTarget = ref(null)
const draftAssignRole = ref('')
const settingsSaving = ref(false)

const settingsTitle = computed(() => {
    if (settingsTarget.value === 'new') {
        return newTag.name.trim() ? `Назначение: ${newTag.name.trim()}` : 'Назначение нового тега'
    }
    return settingsTarget.value?.name ? `Назначение: ${settingsTarget.value.name}` : 'Назначение тега'
})

const assignRoleLabel = (role) => {
    if (role === 'moderator') return 'Модератор'
    if (role === 'admin') return 'Администратор'
    return null
}

const openSettings = (target) => {
    settingsTarget.value = target
    draftAssignRole.value = target === 'new'
        ? (newTag.assign_role || '')
        : (target.assign_role || '')
    settingsOpen.value = true
}

const closeSettings = () => {
    settingsOpen.value = false
    settingsTarget.value = null
    draftAssignRole.value = ''
    settingsSaving.value = false
}

const saveSettings = () => {
    const role = draftAssignRole.value || null

    if (settingsTarget.value === 'new') {
        newTag.assign_role = draftAssignRole.value
        closeSettings()
        return
    }

    const tag = settingsTarget.value
    settingsSaving.value = true
    tag.assign_role = draftAssignRole.value || null

    router.put(
        route('admin.tags.update', tag.id),
        { name: tag.name, description: tag.description || '', assign_role: role },
        {
            preserveScroll: true,
            onSuccess: closeSettings,
            onFinish: () => {
                settingsSaving.value = false
            },
        },
    )
}

const addTag = () => {
    newTag.post(route('admin.tags.store'), {
        onSuccess: () => newTag.reset(),
    })
}

const saveTag = (tag) => {
    router.put(
        route('admin.tags.update', tag.id),
        { name: tag.name, description: tag.description || '', assign_role: tag.assign_role || null },
        { preserveScroll: true },
    )
}

const deleteTag = (tag) => {
    if (!confirm(`Удалить тег «${tag.name}»?`)) return
    router.delete(route('admin.tags.destroy', tag.id), { preserveScroll: true })
}
</script>

<template>
    <PageHead
        title="Теги"
        description="Управление тегами и описаниями в админ-панели портала КИИСА."
    />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <h2>Управление тегами</h2>

            <form class="add-form" @submit.prevent="addTag">
                <div class="split-field split-field--stacked">
                    <input
                        v-model="newTag.name"
                        class="split-input"
                        placeholder="Название тега"
                        required
                    />
                    <input
                        v-model="newTag.description"
                        class="split-input split-input--desc"
                        placeholder="Описание (необязательно)"
                    />
                </div>
                <button
                    type="button"
                    class="row-settings"
                    :class="{
                        'row-settings--moderator': newTag.assign_role === 'moderator',
                        'row-settings--admin': newTag.assign_role === 'admin',
                    }"
                    title="Настройки назначения тега"
                    aria-label="Настройки назначения тега"
                    @click="openSettings('new')"
                >
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke-linecap="round" />
                    </svg>
                </button>
                <button type="submit" class="btn-add btn-accent">Добавить</button>
            </form>

            <ul class="tag-list">
                <li v-for="tag in tags" :key="tag.id" class="tag-row">
                    <div class="split-field split-field--stacked">
                        <input
                            v-model="tag.name"
                            class="split-input"
                            placeholder="Название"
                            @blur="saveTag(tag)"
                        />
                        <input
                            v-model="tag.description"
                            class="split-input split-input--desc"
                            placeholder="Описание (необязательно)"
                            @blur="saveTag(tag)"
                        />
                    </div>
                    <button
                        type="button"
                        class="row-settings"
                        :class="{
                            'row-settings--moderator': tag.assign_role === 'moderator',
                            'row-settings--admin': tag.assign_role === 'admin',
                        }"
                        title="Настройки назначения тега"
                        :aria-label="`Настройки назначения тега ${tag.name}`"
                        @click="openSettings(tag)"
                    >
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke-linecap="round" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="row-delete"
                        aria-label="Удалить тег"
                        @click="deleteTag(tag)"
                    >
                        ×
                    </button>
                </li>
            </ul>
        </div>
    </section>

    <ModalPanel :title="settingsTitle" :open="settingsOpen" @close="closeSettings">
        <p class="settings-intro">
            Кто может назначать этот тег при создании или редактировании статьи.
        </p>

        <div class="role-options">
            <label
                v-for="option in ROLE_OPTIONS"
                :key="option.value || 'all'"
                class="role-option"
                :class="{
                    'role-option--active': draftAssignRole === option.value,
                    'role-option--moderator': option.value === 'moderator',
                    'role-option--admin': option.value === 'admin',
                }"
            >
                <input v-model="draftAssignRole" type="radio" class="role-option__radio" :value="option.value" />
                <span class="role-option__label">{{ option.label }}</span>
                <span class="role-option__hint">{{ option.hint }}</span>
            </label>
        </div>

        <template #footer>
            <button type="button" class="btn-secondary" @click="closeSettings">Отмена</button>
            <button type="button" class="btn-primary" :disabled="settingsSaving" @click="saveSettings">
                Сохранить
            </button>
        </template>
    </ModalPanel>
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
.add-form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    align-items: stretch;
}
.add-form .split-field { flex: 1; }
.split-field {
    display: flex;
    flex-direction: column;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    min-width: 0;
}
.split-field--stacked .split-input--desc {
    border-top: 1px solid #e2e8f0;
    font-size: 0.9rem;
    color: #718096;
}
.split-input {
    border: none;
    outline: none;
    padding: 0.55rem 0.75rem;
    font-size: 0.95rem;
    width: 100%;
    box-sizing: border-box;
    background: transparent;
}
.tag-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
}
.tag-row {
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
    margin-bottom: 0;
    min-width: 0;
}
.tag-row .split-field {
    flex: 1;
    min-width: 0;
}
.row-settings,
.row-delete {
    flex-shrink: 0;
    width: 36px;
    border-radius: 8px;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.row-settings {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #4a5568;
}
.row-settings:hover { background: #edf2f7; }
.row-settings--moderator {
    border-color: #9ae6b4;
    color: #276749;
    background: #f0fff4;
}
.row-settings--admin {
    border-color: #feb2b2;
    color: #c53030;
    background: #fff5f5;
}
.row-delete {
    border: 1px solid #fed7d7;
    background: #fff5f5;
    color: #c53030;
}
.row-delete:hover { background: #fed7d7; }

.settings-intro {
    margin: 0 0 1rem;
    color: #718096;
    line-height: 1.5;
    font-size: 0.925rem;
}
.role-options {
    display: grid;
    gap: 0.65rem;
}
.role-option {
    display: grid;
    grid-template-columns: auto 1fr;
    grid-template-rows: auto auto;
    gap: 0.15rem 0.65rem;
    padding: 0.75rem 0.85rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.role-option__radio {
    grid-row: 1 / span 2;
    align-self: center;
    margin: 0;
}
.role-option__label {
    font-weight: 600;
}
.role-option__hint {
    grid-column: 2;
    font-size: 0.85rem;
    color: #718096;
    line-height: 1.4;
}
.role-option--active {
    border-color: #0db7ff;
    background: #f7fcff;
}
.role-option--active.role-option--moderator {
    border-color: #48bb78;
    background: #f0fff4;
}
.role-option--active.role-option--moderator .role-option__label { color: #276749; }
.role-option--active.role-option--admin {
    border-color: #fc8181;
    background: #fff5f5;
}
.role-option--active.role-option--admin .role-option__label { color: #c53030; }

.btn-primary,
.btn-secondary {
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.1rem;
    font-weight: 700;
    cursor: pointer;
}
.btn-primary {
    background: #0db7ff;
    color: #fff;
}
.btn-primary:disabled { opacity: 0.6; cursor: wait; }
.btn-secondary {
    background: #edf2f7;
    color: #2d3748;
}

[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .split-field {
    background: #1a1a1a;
    border-color: #404040;
}
[data-theme="dark"] .split-input { color: #f0f0f0; }
[data-theme="dark"] .split-field--stacked .split-input--desc {
    border-color: #333;
    color: #aaa;
}
[data-theme="dark"] .row-settings {
    background: #141414;
    border-color: #404040;
    color: #d4d4d4;
}
[data-theme="dark"] .row-settings:hover { background: #1f1f1f; }
[data-theme="dark"] .row-settings--moderator {
    border-color: #276749;
    color: #68d391;
    background: #14261c;
}
[data-theme="dark"] .row-settings--admin {
    border-color: #742a2a;
    color: #fc8181;
    background: #2a1515;
}
[data-theme="dark"] .row-delete {
    background: #2a1515;
    border-color: #553333;
    color: #fc8181;
}
[data-theme="dark"] .settings-intro,
[data-theme="dark"] .role-option__hint { color: #aaa; }
[data-theme="dark"] .role-option {
    border-color: #404040;
    background: #141414;
}
[data-theme="dark"] .role-option--active {
    border-color: #0db7ff;
    background: #0f1a22;
}
[data-theme="dark"] .role-option--active.role-option--moderator {
    border-color: #48bb78;
    background: #14261c;
}
[data-theme="dark"] .role-option--active.role-option--admin {
    border-color: #fc8181;
    background: #2a1515;
}
[data-theme="dark"] .btn-secondary {
    background: #2a2a2a;
    color: #f0f0f0;
}

@media (max-width: 768px) {
    .admin-panel { margin: 1rem auto; padding: 0 1rem 2rem; }
    .admin-top { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 640px) {
    .tag-list {
        grid-template-columns: 1fr;
    }
}
</style>
