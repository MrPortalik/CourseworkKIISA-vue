<script setup>
import { Head, Link } from '@inertiajs/vue3'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'

defineProps({
    pendingArticles: Object,
    allDrafts: Object,
})

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('ru-RU') : '')
</script>

<template>
    <Head title="Админ-панель" />
    <HeaderComponent />

    <section class="admin-panel content-area">
        <div class="admin-top">
            <h1>Админ-панель</h1>
            <nav class="admin-nav">
                <Link :href="route('admin.categories')" class="admin-tab">Категории</Link>
                <Link :href="route('admin.taxonomy')" class="admin-tab">Теги</Link>
            </nav>
        </div>

        <div class="section">
            <h2>Ожидают публикации</h2>
            <div v-if="pendingArticles.data.length" class="articles-list">
                <article v-for="article in pendingArticles.data" :key="article.id" class="admin-card">
                    <img
                        :src="article.banner || '/Assets/noBanner.jpg'"
                        alt=""
                        class="cover-preview"
                    />
                    <div class="card-body">
                        <h3>{{ article.title }}</h3>
                        <p class="meta">{{ article.user?.name }} · {{ formatDate(article.created_at) }}</p>
                        <span class="badge badge--publishable">Публикуется</span>
                    </div>
                    <div class="actions">
                        <Link :href="route('articles.show', article.slug)" class="btn">Просмотр</Link>
                        <Link :href="route('articles.edit', article.slug)" class="btn btn--primary">Модерация</Link>
                    </div>
                </article>
            </div>
            <p v-else class="empty">Нет статей на модерации</p>
        </div>

        <div class="section">
            <h2>Все неопубликованные</h2>
            <div v-if="allDrafts.data.length" class="articles-list">
                <article v-for="article in allDrafts.data" :key="article.id" class="admin-card">
                    <img :src="article.banner || '/Assets/noBanner.jpg'" alt="" class="cover-preview" />
                    <div class="card-body">
                        <h3>{{ article.title }}</h3>
                        <p class="meta">{{ article.user?.name }} · {{ formatDate(article.created_at) }}</p>
                        <span v-if="article.is_publishable" class="badge badge--publishable">Публикуется</span>
                        <span v-else class="badge badge--draft">Черновик</span>
                    </div>
                    <div class="actions">
                        <Link :href="route('articles.show', article.slug)" class="btn">Просмотр</Link>
                        <Link :href="route('articles.edit', article.slug)" class="btn btn--primary">Редактировать</Link>
                    </div>
                </article>
            </div>
            <p v-else class="empty">Нет черновиков</p>
        </div>
    </section>
</template>

<style scoped>
.admin-panel { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem 3rem; }
.admin-panel h1 {
    margin: 0 0 0.75rem;
}
.admin-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
}
.admin-nav {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}
.admin-tab {
    background: #edf2f7;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
    color: #4a5568;
    transition: background 0.2s, color 0.2s;
}
.admin-tab:hover {
    background: #e2e8f0;
    color: #2d3748;
}
.section { margin-bottom: 2.5rem; }
.section h2 {
    margin: 0 0 0.75rem;
}
.articles-list { display: flex; flex-direction: column; gap: 1rem; }
.admin-card { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; }
.cover-preview { width: 72px; aspect-ratio: 9/16; object-fit: cover; border-radius: 4px; flex-shrink: 0; }
.card-body { flex: 1; }
.meta { color: #718096; font-size: 0.875rem; }
.badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; }
.badge--publishable { background: #fef9c3; color: #a16207; }
.badge--draft { background: #fed7d7; color: #c53030; }
.actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; background: #edf2f7; color: #2d3748; font-size: 0.875rem; }
.btn--primary { background: #4299e1; color: #fff; }
.empty { text-align: center; color: #718096; padding: 2rem; background: #f7fafc; border-radius: 8px; }

[data-theme="dark"] .admin-panel { color: #f0f0f0; }
[data-theme="dark"] .admin-card {
    background: var(--theme_black);
    border-color: #333;
}
[data-theme="dark"] .admin-card h3 { color: #f0f0f0; }
[data-theme="dark"] .meta { color: #aaa; }
[data-theme="dark"] .btn {
    background: #2a2a2a;
    color: #f0f0f0;
    border: 1px solid #404040;
}
[data-theme="dark"] .btn--primary {
    background: #3182ce;
    color: #fff;
    border-color: transparent;
}
[data-theme="dark"] .empty {
    background: #141414;
    border: 1px solid #333;
    color: #aaa;
}
[data-theme="dark"] .admin-tab {
    background: #141414;
    color: #ccc;
    border: 1px solid #404040;
}
[data-theme="dark"] .admin-tab:hover {
    background: #1e1e1e;
    color: #f0f0f0;
}
</style>
