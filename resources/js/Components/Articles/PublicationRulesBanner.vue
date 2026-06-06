<script setup>
import { ref } from 'vue'
import ModalPanel from '@/Components/ModalPanel.vue'

const open = ref(false)
</script>

<template>
    <div class="rules-banner">
        <p>
            Перед публикацией ознакомьтесь с
            <button type="button" class="rules-link" @click="open = true">
                правилами платформы о публикации материалов
            </button>.
        </p>

        <ModalPanel
            title="Правила платформы о публикации материалов"
            :open="open"
            @close="open = false"
        >
            <ul class="rules-list">
                <li>Соблюдайте правила поведения и уважайте других участников портала.</li>
                <li>Публикуйте серьёзные, осмысленные материалы, соответствующие тематике вселенной КИИСА.</li>
                <li>Соблюдайте стилистику повествования: связный текст, грамотность, логичная структура.</li>
                <li>Не размещайте оскорбительный, незаконный или вводящий в заблуждение контент.</li>
                <li>Используйте только те изображения, на которые у вас есть права.</li>
                <li>Материалы проходят модерацию: администрация может отклонить или запросить доработку.</li>
            </ul>
            <template #footer>
                <button type="button" class="rules-close-btn" @click="open = false">Понятно</button>
            </template>
        </ModalPanel>
    </div>
</template>

<style scoped>
.rules-banner {
    margin-bottom: 1.25rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: rgba(13, 183, 255, 0.1);
    border: 1px solid rgba(13, 183, 255, 0.35);
    color: #2d3748;
    font-size: 0.95rem;
}

.rules-link {
    background: none;
    border: none;
    padding: 0;
    color: #0db7ff;
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
}

.rules-list {
    margin: 0;
    padding-left: 1.2rem;
    display: grid;
    gap: 0.65rem;
    line-height: 1.55;
}

.rules-close-btn {
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.25rem;
    font-weight: 700;
    cursor: pointer;
}

[data-theme="dark"] .rules-banner {
    color: #f0f0f0;
    background: rgba(13, 183, 255, 0.12);
}
</style>
