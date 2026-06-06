<script setup>
import PageHead from '@/Components/PageHead.vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'

defineProps({
    faqs: Array,
})
</script>

<template>
    <PageHead
        title="FAQ"
        description="Ответы на частые вопросы о проекте КИИСА, публикации статей, модерации и правилах портала."
    />
    <PageWithSidebar>
        <h1>FAQ</h1>
        <p class="intro">Ответы на частые вопросы о проекте КИИСА.</p>

        <div v-if="faqs.length" class="faq-accordion">
            <details v-for="item in faqs" :key="item.id" class="faq-item">
                <summary>{{ item.question }}</summary>
                <div class="faq-answer">
                    <p>{{ item.answer }}</p>
                </div>
            </details>
        </div>
        <p v-else class="empty">Вопросы пока не добавлены в базу данных.</p>
    </PageWithSidebar>
</template>

<style scoped>
h1 { margin-bottom: 0.5rem; }
.intro { margin-bottom: 2rem; font-size: 1.1rem; color: #718096; }
.faq-accordion { max-width: 800px; }
.faq-item {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 0.75rem;
    overflow: hidden;
    background: #fff;
}
.faq-item summary {
    padding: 1rem 1.25rem;
    font-weight: 600;
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary::after {
    content: '+';
    font-size: 1.25rem;
    transition: transform 0.25s ease;
}
.faq-item[open] summary::after {
    transform: rotate(45deg);
}
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.35s ease;
}
.faq-item[open] .faq-answer {
    max-height: 600px;
}
.faq-answer p {
    margin: 0;
    padding: 0 1.25rem;
    font-size: 1rem;
    line-height: 1.6;
    color: #4a5568;
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity 0.3s ease 0.05s, transform 0.3s ease 0.05s, padding 0.35s ease;
}
.faq-item[open] .faq-answer p {
    padding-top: 0.25rem;
    padding-bottom: 1rem;
    opacity: 1;
    transform: translateY(0);
}
.empty { color: #718096; }
[data-theme="dark"] .faq-item { background: #111; border-color: #333; }
[data-theme="dark"] .faq-answer p { color: #ccc; }
</style>
