<script setup>
import { Head, Link } from '@inertiajs/vue3'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'

defineProps({
    authors: Array,
})
</script>

<template>
    <Head title="Подписки" />
    <HeaderComponent />

    <section class="subscriptions-page">
        <h1>Мои подписки</h1>

        <ul v-if="authors.length" class="authors-list">
            <li v-for="author in authors" :key="author.id" class="author-item">
                <div>
                    <h3>{{ author.name }}</h3>
                    <p class="meta">Опубликовано статей: {{ author.published_articles_count }}</p>
                </div>
                <Link :href="route('authors.show', author.id)" class="btn">Профиль автора</Link>
            </li>
        </ul>

        <p v-else class="empty">Вы пока ни на кого не подписаны</p>
    </section>
</template>

<style scoped>
.subscriptions-page {
    max-width: 700px;
    margin: 2rem auto;
    padding: 0 1.5rem 3rem;
}

.authors-list {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0 0;
}

.author-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    background: white;
}

.author-item h3 {
    margin: 0 0 0.25rem;
}

.meta {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.btn {
    background: #4299e1;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    font-weight: 500;
}

.empty {
    color: #718096;
    margin-top: 2rem;
}
</style>
