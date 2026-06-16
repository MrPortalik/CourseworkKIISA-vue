<script setup>
import { router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'

defineProps({
    tags: Array,
})

const newTag = useForm({ name: '', description: '' })

const addTag = () => {
    newTag.post(route('admin.tags.store'), {
        onSuccess: () => newTag.reset(),
    })
}

const saveTag = (tag) => {
    router.put(
        route('admin.tags.update', tag.id),
        { name: tag.name, description: tag.description || '' },
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
.row-delete {
    flex-shrink: 0;
    width: 36px;
    border: 1px solid #fed7d7;
    background: #fff5f5;
    color: #c53030;
    border-radius: 8px;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
}
.row-delete:hover { background: #fed7d7; }
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
[data-theme="dark"] .row-delete {
    background: #2a1515;
    border-color: #553333;
    color: #fc8181;
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
