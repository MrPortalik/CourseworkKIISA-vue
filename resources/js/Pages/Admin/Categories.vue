<script setup>
import { router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import AdminNav from '@/Components/Admin/AdminNav.vue'

defineProps({
    categories: Array,
})

const newCategory = useForm({ name: '', description: '' })

const addCategory = () => {
    newCategory.post(route('admin.categories.store'), {
        onSuccess: () => newCategory.reset(),
    })
}

const saveCategory = (cat) => {
    router.put(route('admin.categories.update', cat.id), {
        name: cat.name,
        description: cat.description || '',
    }, { preserveScroll: true })
}

const deleteCategory = (cat) => {
    if (!confirm(`Удалить категорию «${cat.name}»?`)) return
    router.delete(route('admin.categories.destroy', cat.id), { preserveScroll: true })
}
</script>

<template>
    <PageHead
        title="Категории"
        description="Управление категориями статей в админ-панели портала КИИСА."
    />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <AdminNav />
        </div>

        <div class="section">
            <h2>Управление категориями</h2>

            <form class="add-form" @submit.prevent="addCategory">
                <div class="split-field split-field--new">
                    <input
                        v-model="newCategory.name"
                        class="split-input split-input--top"
                        placeholder="Название категории"
                        required
                    />
                    <input
                        v-model="newCategory.description"
                        class="split-input split-input--bottom"
                        placeholder="Описание"
                    />
                </div>
                <button type="submit" class="btn-add btn-accent">Добавить</button>
            </form>

            <ul class="category-list">
                <li v-for="cat in categories" :key="cat.id" class="category-row">
                    <div class="split-field">
                        <input
                            v-model="cat.name"
                            class="split-input split-input--top"
                            @blur="saveCategory(cat)"
                        />
                        <input
                            v-model="cat.description"
                            class="split-input split-input--bottom"
                            placeholder="Описание"
                            @blur="saveCategory(cat)"
                        />
                    </div>
                    <button
                        type="button"
                        class="row-delete"
                        aria-label="Удалить категорию"
                        @click="deleteCategory(cat)"
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
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
}
.section { margin-bottom: 2.5rem; }
.section h2 { margin: 0 0 0.75rem; }
.add-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}
.split-field {
    display: flex;
    flex-direction: column;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    flex: 1;
    min-width: 0;
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
.split-input--top {
    border-bottom: 1px solid #e2e8f0;
}
.split-input--bottom::placeholder {
    color: #a0aec0;
}
.category-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
}
.category-row {
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
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
    align-self: stretch;
}
.row-delete:hover {
    background: #fed7d7;
}
.btn-add {
    background: #4299e1;
    color: #fff;
    border: none;
    padding: 0.6rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .split-field {
    background: #1a1a1a;
    border-color: #404040;
}
[data-theme="dark"] .split-input {
    color: #f0f0f0;
}
[data-theme="dark"] .split-input--top {
    border-bottom-color: #404040;
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
</style>
