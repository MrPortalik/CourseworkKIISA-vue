<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'

const props = defineProps({
    status: { type: Number, required: true },
})

const errorContent = {
    403: {
        title: 'Доступ запрещён',
        heading: '403',
        message: 'У вас нет прав для просмотра этой страницы.',
        description: 'У вас нет прав для просмотра этой страницы на портале КИИСА.',
    },
    404: {
        title: 'Страница не найдена',
        heading: '404',
        message: 'Запрашиваемая страница не существует или была удалена.',
        description: 'Запрашиваемая страница не найдена на портале КИИСА. Вернитесь на главную или в каталог статей.',
    },
    500: {
        title: 'Ошибка сервера',
        heading: '500',
        message: 'На сервере произошла ошибка. Попробуйте обновить страницу.',
        description: 'На сервере портала КИИСА произошла ошибка. Попробуйте обновить страницу или вернитесь на главную.',
    },
    503: {
        title: 'Сайт недоступен',
        heading: '503',
        message: 'Портал временно недоступен из-за технических работ.',
        description: 'Портал КИИСА временно недоступен из-за технических работ. Пожалуйста, зайдите позже.',
    },
}

const content = computed(() => errorContent[props.status] ?? errorContent[500])

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back()
        return
    }

    window.location.href = route('/')
}
</script>

<template>
    <PageHead :title="content.title" :description="content.description" />
    <HeaderComponent />

    <main class="error-page content-area">
        <div class="error-card">
            <p class="error-code">{{ content.heading }}</p>
            <h1>{{ content.title }}</h1>
            <p class="error-message">{{ content.message }}</p>

            <div class="error-actions">
                <Link :href="route('/')" class="error-btn error-btn--primary">На главную</Link>
                <button type="button" class="error-btn error-btn--secondary" @click="goBack">Назад</button>
            </div>
        </div>
    </main>
</template>

<style scoped>
.error-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 130px);
    padding: 2rem 1.5rem 4rem;
}

.error-card {
    width: 100%;
    max-width: 560px;
    text-align: center;
    background: var(--page-bg);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 2.5rem 2rem;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
}

.error-code {
    margin: 0 0 0.5rem;
    font-family: "Oswald", sans-serif;
    font-size: clamp(4rem, 14vw, 6rem);
    line-height: 1;
    font-weight: 400;
    color: #0db7ff;
}

.error-card h1 {
    margin: 0 0 1rem;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
}

.error-message {
    margin: 0 0 2rem;
    color: #718096;
    font-size: 1.05rem;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
}

.error-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 160px;
    padding: 0.85rem 1.5rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.error-btn--primary {
    background: #0db7ff;
    color: #fff;
}

.error-btn--primary:hover {
    background: #0aa5e8;
}

.error-btn--secondary {
    background: transparent;
    color: var(--page-text);
    border: 1px solid #cbd5e0;
}

.error-btn--secondary:hover {
    border-color: #0db7ff;
    color: #0db7ff;
}

[data-theme="dark"] .error-card {
    background: #141414;
    border-color: #333;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}

[data-theme="dark"] .error-message {
    color: #aaa;
}

[data-theme="dark"] .error-btn--secondary {
    border-color: #444;
}

@media (max-width: 768px) {
    .error-page {
        min-height: calc(100vh - 72px);
        padding: 1.25rem 1rem 2rem;
    }

    .error-card {
        padding: 2rem 1.25rem;
    }

    .error-btn {
        width: 100%;
    }
}
</style>
