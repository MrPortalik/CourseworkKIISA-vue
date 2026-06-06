<script setup>
import { router, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { ref } from 'vue'

defineProps({
    categories: Array,
})

const showModal = ref(true)

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

const close = () => {
    showModal.value = false
    router.visit(route('admin.index'))
}
</script>

<template>
    <PageHead
        title="Категории"
        description="Управление категориями статей в админ-панели портала КИИСА."
    />
    <HeaderComponent />

    <div v-if="showModal" class="modal-overlay">
        <div class="modal-window" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h2>Управление категориями</h2>
                <button type="button" class="close-x" aria-label="Закрыть" @click="close">×</button>
            </div>

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
.btn-close {
    width: 100%;
    padding: 0.65rem;
    background: #edf2f7;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .modal-window {
    background: #141414;
    border: 1px solid #333;
    color: #f0f0f0;
}
[data-theme="dark"] .modal-header h2,
[data-theme="dark"] .close-x { color: #f0f0f0; }
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
[data-theme="dark"] .btn-close {
    background: #2a2a2a;
    color: #f0f0f0;
}
</style>
