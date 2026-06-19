<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="rules-banner">
        <p>
            Перед публикацией ознакомьтесь с
            <Link :href="route('publication-rules')" class="rules-link" target="_blank">
                правилами платформы о публикации материалов
            </Link>.
        </p>
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
    color: #0db7ff;
    font-weight: 700;
    text-decoration: underline;
}

[data-theme="dark"] .rules-banner {
    color: #f0f0f0;
    background: rgba(13, 183, 255, 0.12);
}
</style>
