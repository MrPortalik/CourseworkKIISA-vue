<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue';
import SideMenuComponent from '@/Layouts/SideMenuComponent.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    article: Object,
})

// Получаем доступ к данным страницы
const page = usePage()

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getParagraphs = (content) => {
    if (!content) return []
    return content
        .split('\n')
        .map(p => p.trim())
        .filter(p => p.length > 0)
}

// Удаление статьи
const deleteArticle = () => {
    if (confirm('Вы уверены, что хотите удалить эту статью?')) {
        router.delete(route('articles.destroy', props.article.slug))
    }
}

// Проверка, является ли текущий пользователь автором статьи (используем computed)
const isAuthor = computed(() => {
    // Проверяем есть ли пользователь и совпадает ли ID
    const currentUser = page.props.auth?.user
    return currentUser && currentUser.id === props.article.user_id
})
</script>

<template>
    <Head>
        <title>{{ article.title }}</title>
    </Head>
    
    <HeaderComponent/>
    <div class="divider">
        <SideMenuComponent/>

        <section class="article">
            <h1>{{ article.title }}</h1> 
            
            <!-- Информация о статье -->
            <div class="article-meta">
                <span class="author">Автор: {{ article.user?.name }}</span>
                <span class="date">Опубликовано: {{ formatDate(article.created_at) }}</span>
                
                <!-- Бейдж черновика -->
                <span v-if="!article.is_published" class="draft-badge">
                    Черновик
                </span>
                
                <!-- Кнопки редактирования (только для автора) -->
                <div v-if="isAuthor" class="article-actions">
                    <Link 
                        :href="route('articles.edit', article.slug)"
                        class="edit-btn"
                    >
                        Редактировать
                    </Link>
                    <button 
                        @click="deleteArticle"
                        class="delete-btn"
                    >
                        Удалить
                    </button>
                </div>
            </div>
            
            <div class="article-content">
                <!-- Разбиваем контент на параграфы -->
                <p 
                    v-for="(paragraph, index) in getParagraphs(article.content)" 
                    :key="index"
                    class="content-paragraph">
                    {{ paragraph }}
                </p>
            </div>
            
            <!-- Навигация -->
            <div class="article-navigation">
                <Link :href="route('articles.index')" class="back-link">
                    ← Назад к статьям
                </Link>
            </div>
        </section> 
    </div>
</template>

<style>
    .article {
        margin-top: 50px;
        margin-left: 50px;
        margin-right: 150px;
        width: 1418px;
        max-width: 100% !important;
        justify-self: end;
    }

    .article h1 {
        margin-bottom: 25px;
        font-size: 2.5rem;
        color: #333;
    }

    .article-meta {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .author, .date {
        color: #666;
        font-size: 14px;
    }

    .draft-badge {
        background: #ffeb3b;
        color: #333;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .article-actions {
        margin-left: auto;
        display: flex;
        gap: 10px;
    }

    .edit-btn {
        background: #4a90e2;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .edit-btn:hover {
        background: #357ae8;
    }

    .delete-btn {
        background: #f44336;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .delete-btn:hover {
        background: #d32f2f;
    }

    .article-content {
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 40px;
    }

    .article p {
        margin-bottom: 20px;
        text-indent: 4%;
    }

    .article-navigation {
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .back-link {
        color: #4a90e2;
        text-decoration: none;
        font-weight: 500;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .divider {
        margin: 0;
        padding: 0;
        display: flex;
    }
</style>