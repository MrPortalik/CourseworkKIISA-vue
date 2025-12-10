<template>
  <div class="articles-index">
    <Head title="Статьи" />
    <HeaderComponent/>
    <div class="divider">
        <SideMenuComponent/>
    
        <section class="articleIndex">
                <div class="page-header">
                <h1>Статьи</h1>
                <Link 
                v-if="$page.props.auth?.user"
                :href="route('articles.create')"
                class="create-button"
                >
                Предложить статью
                </Link>
            </div>
            
            <div v-if="articles.data.length" class="articles-grid">
            <article 
                v-for="article in articles.data" 
                :key="article.id" 
                class="article-card"
            >
                <Link :href="route('articles.show', article.slug)" class="card-link">
                <h2 class="card-title">{{ article.title }}</h2>
                <div class="card-meta">
                    <span class="author">{{ article.user?.name }}</span>
                    <span class="date">{{ formatDate(article.created_at) }}</span>
                </div>
                <div v-if="!article.is_published" class="card-draft">
                        Черновик
                </div>
                </Link>
            </article>
            </div>
            
            <div v-else class="empty-state">
            <p>Статей пока нет</p>
            </div>
            
            <!-- Пагинация -->
            <div v-if="articles.data.length && articles.links.length > 3" class="pagination">
            <Link 
                v-for="(link, index) in articles.links"
                :key="index"
                :href="link.url || '#'"
                class="page-link"
                :class="{
                'active': link.active,
                'disabled': !link.url
                }"
                v-html="link.label"
                :preserve-scroll="true"
            />
            </div>
        </section>
    </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SideMenuComponent from '@/Layouts/SideMenuComponent.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'

const props = defineProps({
  articles: Object,
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU')
}
</script>

<style scoped>
.divider {
    margin: 0 0;
    padding: 0 0;
    display: flex;
}

.articleIndex {
    margin: 50px;
    width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.create-button {
  background-color: #4299e1;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.2s;
}

.create-button:hover {
  background-color: #3182ce;
}

.articles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.article-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  overflow: hidden;
  transition: all 0.2s;
}

.article-card:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border-color: #cbd5e0;
  color: #646464;
}

.card-link {
  display: block;
  padding: 1.5rem;
  text-decoration: none;
  color: inherit;
  position: relative;
  height: 100%;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  line-height: 1.4;
  color: #2d3748;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
  color: #718096;
  margin-top: auto;
}

.card-draft {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background-color: #fed7d7;
  color: #c53030;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.empty-state {
  text-align: center;
  padding: 4rem 1rem;
  color: #718096;
  font-size: 1.125rem;
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
  transition: all 0.2s;
}

.page-link:hover:not(.active):not(.disabled) {
  background-color: #f7fafc;
  border-color: #cbd5e0;
}

.page-link.active {
  background-color: #4299e1;
  color: white;
  border-color: #4299e1;
}

.page-link.disabled {
  color: #a0aec0;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Адаптивность */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .articles-grid {
    grid-template-columns: 1fr;
  }
  
  .pagination {
    flex-wrap: wrap;
  }
}
</style>