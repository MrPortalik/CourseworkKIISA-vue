<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SideMenuComponent from '@/Layouts/SideMenuComponent.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleCard from '@/Components/ArticleCard.vue'

defineProps({
    articles: Object,
    isAdmin: Boolean,
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU')
}
</script>

<template>
    <Head title="Наброски" />
    <HeaderComponent />
    <div class="divider content-area">
        <SideMenuComponent />

        <section class="articleIndex page-main-inner">
            <div class="page-header">
                <h1>{{ isAdmin ? 'Все наброски' : 'Ваши наброски' }}</h1>
                <nav class="actions">
                    <Link :href="route('articles.create')" class="action-button">Создать статью</Link>
                    <Link :href="route('articles.index')" class="action-button secondary">К статьям</Link>
                </nav>
            </div>

            <div v-if="articles.data.length" class="articles-grid">
                <ArticleCard v-for="article in articles.data" :key="article.id" :article="article" />
            </div>

            <div v-else class="empty-state">
                <p>Черновиков пока нет</p>
            </div>

            <nav v-if="articles.data.length && articles.links.length > 3" class="pagination">
                <Link
                    v-for="(link, index) in articles.links"
                    :key="index"
                    :href="link.url || '#'"
                    class="page-link"
                    :class="{ active: link.active, disabled: !link.url }"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </nav>
        </section>
    </div>
</template>

<style scoped>
.divider.content-area {
    display: flex;
    width: 100%;
}

.page-main-inner {
    padding: 2rem 2.5rem 3rem;
    width: 100%;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.actions {
    display: flex;
    gap: 1rem;
}

.action-button {
    background-color: var(--theme_black);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
}

.action-button.secondary {
    background-color: #718096;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.article-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    transition: box-shadow 0.2s;
}

.article-card:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.card-link {
    display: block;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    position: relative;
}

.card-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #2d3748;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: #718096;
}

.card-draft {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.card-draft--plain {
    background-color: #fed7d7;
    color: #c53030;
}

.card-draft--publishable {
    background-color: #fef9c3;
    color: #a16207;
    border: 1px solid #fde047;
}

.empty-state {
    text-align: center;
    padding: 4rem;
    color: #718096;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
}

.page-link {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    text-decoration: none;
    color: #4a5568;
}

.page-link.active {
    background-color: #4299e1;
    color: white;
}

.page-link.disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>
