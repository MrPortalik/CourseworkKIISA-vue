<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import PageHead from '@/Components/PageHead.vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import FeedbackModal from '@/Components/FeedbackModal.vue'

const feedbackOpen = ref(false)
</script>

<template>
    <PageHead
        title="О нас"
        description="Творческое объединение «Элепортиевы грёзы» и портал КИИСА: каноничные истории, модерация и сообщество авторов."
    />
    <PageWithSidebar>
        <h1>О нас</h1>
        <p class="intro">
            Мы творческое объединение "Элепортиевы грёзы" и с 2024 года работаем над сюжетом собственной вселенной с
            низким порогом вхождения.
            КИИСА — один из проектов нашего глобального сюжета. На портале публикуются официальные и утверждённые
            пользовательские статьи, часто находящиеся в рамках каноничного сюжета; модерацией проекта занимается администрация портала.
        </p>

        <div class="about-actions">
            <Link :href="route('faq.index')" class="faq-link actionBtn">FAQ</Link>
            <button type="button" class="feedback-link actionBtn" @click="feedbackOpen = true">Обратная связь</button>
        </div>

        <FeedbackModal :open="feedbackOpen" @close="feedbackOpen = false" />
    </PageWithSidebar>
</template>

<style scoped>
h1 { margin-bottom: 0.5rem; }
.intro {
    max-width: 800px;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
    color: #718096;
}
.about-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.feedback-link {
    border: none;
    cursor: pointer;
}

.actionBtn {
    display: inline-flex;
    margin-top: 0.5rem;
    padding: 16px 60px;
    background: #0db7ff;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.25rem;
    color: white;
    text-decoration: none;
}
[data-theme="dark"] .intro { color: #aaa; }
</style>
