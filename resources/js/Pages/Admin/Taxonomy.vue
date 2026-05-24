<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'

defineProps({
    tags: Array,
})

const newTag = useForm({ name: '' })
const addTag = () => newTag.post(route('admin.tags.store'), { onSuccess: () => newTag.reset() })
const saveTag = (tag) => router.put(route('admin.tags.update', tag.id), { name: tag.name }, { preserveScroll: true })
</script>

<template>
    <Head title="Теги" />
    <HeaderComponent />

    <section class="taxonomy-page">
        <Link :href="route('admin.index')" class="content-link back">← Админ-панель</Link>
        <h1>Теги</h1>
        <form class="add-form" @submit.prevent="addTag">
            <input v-model="newTag.name" placeholder="Новый тег" required />
            <button type="submit">Добавить</button>
        </form>
        <div v-for="tag in tags" :key="tag.id" class="row">
            <input v-model="tag.name" @blur="saveTag(tag)" />
        </div>
    </section>
</template>

<style scoped>
.taxonomy-page { max-width: 500px; margin: 2rem auto; padding: 0 1.5rem; }
.add-form, .row { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; }
input { flex: 1; padding: 0.5rem; border: 1px solid #cbd5e0; border-radius: 6px; }
button { background: #4299e1; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }
.back { display: inline-block; margin-bottom: 1rem; }
[data-theme="dark"] .taxonomy-page { color: #f0f0f0; }
[data-theme="dark"] input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
</style>
