<script setup>
import { Link, router } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import ArticleCard from '@/Components/Articles/ArticleCard.vue'

const props = defineProps({
    articles: Object,
    isAdmin: Boolean,
    scope: { type: String, default: 'mine' },
})

const setScope = (nextScope) => {
    if (nextScope === props.scope) {
        return
    }

    router.get(route('articles.drafts'), { scope: nextScope }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const pageTitle = () => {
    if (!props.isAdmin) {
        return 'Ваши наброски'
    }
    return props.scope === 'all' ? 'Все наброски' : 'Свои наброски'
}
</script>

<template>
    <PageHead
        title="Наброски"
        description="Черновики ваших статей на портале КИИСА: продолжите редактирование или отправьте материал на модерацию."
    />

    <PageWithSidebar>
        <div class="page-header">
            <div class="title-block">
                <h1>{{ pageTitle() }}</h1>
                <nav v-if="isAdmin" class="scope-tabs" aria-label="Раздел набросков">
                    <button
                        type="button"
                        class="scope-tab"
                        :class="{ active: scope === 'all' }"
                        @click="setScope('all')"
                    >
                        Все наброски
                    </button>
                    <button
                        type="button"
                        class="scope-tab"
                        :class="{ active: scope === 'mine' }"
                        @click="setScope('mine')"
                    >
                        Свои наброски
                    </button>
                </nav>
            </div>
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
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.title-block {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.scope-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.scope-tab {
    background: #edf2f7;
    border: none;
    padding: 0.45rem 0.9rem;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.875rem;
    color: #4a5568;
    font-family: inherit;
}

.scope-tab.active {
    background: var(--theme_black);
    color: #ffffff;
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

[data-theme="dark"] .scope-tab {
    background: #141414;
    color: #ccc;
    border: 1px solid #404040;
}

[data-theme="dark"] .scope-tab.active {
    background: transparent;
    color: #ffffff;
    box-shadow: 0 0 0 2px #ffffff;
    border-color: transparent;
}
</style>
