<template>
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
            <p>Черновиков пока нет</p>
            </div>
            <!-- to-do если нет черновиков, то сделать пустую статью с кнопкой плюса -->
            
            <!-- Пагинация -->
            <nav v-if="articles.data.length && articles.links.length > 3" class="pagination">
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
            </nav>
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