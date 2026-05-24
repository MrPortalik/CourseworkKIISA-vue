<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
</script>

<template>
    <Head title="О нас" />
    <PageWithSidebar>
        <h1>О нас</h1>
        <p class="intro">
            Мы творческое объединение и с 2024 года работаем над сюжетом вселенной с низким порогом вхождения.
            КИИСА — один из проектов глобального сюжета. На портале публикуются официальные и утверждённые
            пользовательские статьи; модерацией занимается администрация.
        </p>

        <Link :href="route('faq.index')" class="faq-link actionBtn">FAQ</Link>
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
