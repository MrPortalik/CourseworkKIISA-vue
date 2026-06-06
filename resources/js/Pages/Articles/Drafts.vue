<script setup>
import { Link } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import ArticleCard from '@/Components/Articles/ArticleCard.vue'

defineProps({
    articles: Object,
    isAdmin: Boolean,
})
</script>

<template>
    <PageHead
        title="Наброски"
        description="Черновики ваших статей на портале КИИСА: продолжите редактирование или отправьте материал на модерацию."
    />

    <PageWithSidebar>
        <div class="page-header">
            <h1>{{ isAdmin ? 'Все наброски' : 'Ваши наброски' }}</h1>
            <nav class="actions">
                <Link :href="route('articles.create')" class="page-cta page-cta--primary">Создать статью</Link>
                <Link :href="route('articles.index')" class="page-cta page-cta--primary">К статьям</Link>
            </nav>
        </div>

        <div v-if="articles.data.length" class="articles-grid articles-grid--7">
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
    </PageWithSidebar>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    color: #718096;
}

.pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
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
