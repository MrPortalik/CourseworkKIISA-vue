<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
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

const close = () => {
    showModal.value = false
    router.visit(route('admin.index'))
}
</script>

<template>
    <Head title="Категории" />
    <HeaderComponent />

    <div v-if="showModal" class="modal-overlay" @click.self="close">
        <div class="modal-window">
            <div class="modal-header">
                <h2>Управление категориями</h2>
                <button type="button" class="close-x" @click="close">×</button>
            </div>

            <form class="add-form" @submit.prevent="addCategory">
                <input v-model="newCategory.name" placeholder="Название категории" required />
                <input v-model="newCategory.description" placeholder="Описание" />
                <button type="submit" class="btn-add">Добавить</button>
            </form>

            <ul class="category-list">
                <li v-for="cat in categories" :key="cat.id">
                    <input v-model="cat.name" @blur="saveCategory(cat)" />
                    <input v-model="cat.description" placeholder="Описание" @blur="saveCategory(cat)" />
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
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.modal-header h2 { margin: 0; font-size: 1.35rem; }
.close-x { background: none; border: none; font-size: 1.75rem; cursor: pointer; line-height: 1; }
.add-form { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.25rem; }
.add-form input { padding: 0.6rem; border: 1px solid #cbd5e0; border-radius: 8px; }
.btn-add { background: #4299e1; color: #fff; border: none; padding: 0.6rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
.category-list { list-style: none; padding: 0; margin: 0 0 1rem; }
.category-list li { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.75rem; }
.category-list input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.btn-close { width: 100%; padding: 0.65rem; background: #edf2f7; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
[data-theme="dark"] .modal-window {
    background: #141414;
    border: 1px solid #333;
    color: #f0f0f0;
}
[data-theme="dark"] .modal-header h2,
[data-theme="dark"] .close-x { color: #f0f0f0; }
[data-theme="dark"] .add-form input,
[data-theme="dark"] .category-list input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .btn-close { background: #2a2a2a; color: #f0f0f0; }
</style>
