<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import ArticleSearchBar from '@/Components/ArticleSearchBar.vue'
import ArticleCard from '@/Components/ArticleCard.vue'
import CategoryArticleSliders from '@/Components/CategoryArticleSliders.vue'

const props = defineProps({
    articles: Object,
    exactArticles: Object,
    otherArticles: Object,
    searchMeta: Object,
    filters: Object,
    categories: Array,
    tags: Array,
    objectRange: Object,
    activeCategory: Object,
    categorySliders: Array,
})

const previewOrder = ref([])
const allArticlesOpen = ref(false)

const isFirstPage = computed(() => (props.articles?.current_page ?? 1) === 1)

const showCategorySliders = computed(() =>
    props.categorySliders?.length
    && isFirstPage.value
    && !props.filters?.search
    && !props.filters?.category
    && !props.filters?.section
    && !props.objectRange
)

const displayArticles = computed(() => {
    const list = props.articles?.data ?? []
    if (!previewOrder.value.length || props.filters?.search) {
        return list
    }
    const map = Object.fromEntries(list.map((a) => [a.id, a]))
    const ordered = previewOrder.value.map((id) => map[id]).filter(Boolean)
    const rest = list.filter((a) => !previewOrder.value.includes(a.id))
    return [...ordered, ...rest]
})

const onPreview = (items) => {
    previewOrder.value = items.map((i) => i.id)
}

const showArticlesGrid = computed(() =>
    !showCategorySliders.value || allArticlesOpen.value
)

const showPagination = computed(() => (props.articles?.last_page ?? 1) > 1)

const paginationLinks = computed(() => {
    const links = props.articles?.links ?? []
    const lastPage = props.articles?.last_page ?? 1

    return links.filter((link) => {
        if (!link.url && !link.active) return false
        const match = String(link.label).match(/^\d+$/)
        if (match) {
            const pageNum = parseInt(match[0], 10)
            return pageNum >= 1 && pageNum <= lastPage
        }
        return true
    })
})

watch(() => props.filters?.q, (q) => {
    if (!q) previewOrder.value = []
})

watch(showCategorySliders, (visible) => {
    if (!visible) allArticlesOpen.value = true
})

const toggleAllArticles = () => {
    allArticlesOpen.value = !allArticlesOpen.value
}

const gotoCategory = (categoryId) => {
    router.get(route('articles.index', { category: categoryId }))
}
</script>

<template>
    <Head :title="activeCategory?.name || (objectRange ? `Объекты ${objectRange.label}` : 'Статьи')" />

    <PageWithSidebar>
        <div class="page-header">
            <h1>
                {{ activeCategory?.name || (objectRange ? `Объекты (${objectRange.label})` : 'Статьи') }}
            </h1>
            <nav class="actions">
                <Link v-if="$page.props.auth?.user" :href="route('articles.create')" class="action-button">Создать статью</Link>
                <Link v-if="$page.props.auth?.user" :href="route('articles.drafts')" class="action-button secondary">Наброски</Link>
            </nav>
        </div>

        <ArticleSearchBar :filters="filters" :tags="tags" :categories="categories" @preview="onPreview" />

        <CategoryArticleSliders
            v-if="showCategorySliders"
            :sliders="categorySliders"
            :all-articles-open="allArticlesOpen"
            @toggle-all-articles="toggleAllArticles"
            @goto-category="gotoCategory"
        />

        <template v-if="filters.search && filters.q">
            <template v-if="exactArticles?.data?.length">
                <h2 class="section-heading">Наибольшее совпадение</h2>
                <div class="articles-grid">
                    <ArticleCard v-for="article in exactArticles.data" :key="article.id" :article="article" />
                </div>
            </template>

            <template v-if="searchMeta?.showOthers && otherArticles?.data?.length">
                <h2 class="section-heading">{{ searchMeta?.hasExact ? 'Другие совпадения' : 'Наибольшее совпадение' }}</h2>
                <div class="articles-grid">
                    <ArticleCard v-for="article in otherArticles.data" :key="'o-' + article.id" :article="article" />
                </div>
            </template>

            <p
                v-if="!exactArticles?.data?.length && !otherArticles?.data?.length"
                class="empty-state"
            >
                Ничего не найдено
            </p>
        </template>

        <template v-else>
            <div
                v-show="showArticlesGrid"
                id="all-articles-grid"
                class="all-articles-section"
            >
                <h2 v-if="showCategorySliders" class="section-heading">Все статьи</h2>
                <div v-if="displayArticles.length" class="articles-grid">
                    <ArticleCard v-for="article in displayArticles" :key="article.id" :article="article" />
                </div>
                <p v-else class="empty-state">Статей пока нет</p>

                <nav v-if="showPagination && paginationLinks.length" class="pagination">
                    <Link
                        v-for="(link, index) in paginationLinks"
                        :key="`${link.label}-${index}`"
                        :href="link.url || '#'"
                        class="page-link"
                        :class="{ active: link.active, disabled: !link.url }"
                        preserve-scroll
                        v-html="link.label"
                    />
                </nav>
            </div>
        </template>
    </PageWithSidebar>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.actions { display: flex; gap: 0.75rem; }
.action-button {
    background: var(--theme_black);
    color: white;
    padding: 0.65rem 1.25rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
}
.action-button.secondary { background: var(--theme_black); }
.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
    align-items: stretch;
}
.all-articles-section {
    width: 100%;
    max-width: 100%;
}
.section-heading { font-size: 1.5rem; margin: 0 0 1rem; }
.empty-state { text-align: center; color: #718096; padding: 3rem; }
.pagination { display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap; }
.page-link {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    text-decoration: none;
    color: #4a5568;
}
.page-link.active { background: #4299e1; color: white; }
</style>
