<script setup>
defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: 'Обложка' },
})
</script>

<template>
    <img
        v-if="src"
        :src="src"
        :alt="alt"
        class="article-cover-thumb"
    />
    <div v-else class="article-cover-thumb article-cover-thumb--empty" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M5 4h10a2 2 0 0 1 2 2v14H7a2 2 0 0 1-2-2V4z" stroke="currentColor" stroke-width="1.5" />
            <path d="M7 4v16M17 6h2a1 1 0 0 1 1 1v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
    </div>
</template>

<style scoped>
.article-cover-thumb {
    width: 100%;
    max-width: 130px;
    aspect-ratio: 9 / 16;
    object-fit: cover;
    border-radius: 4px;
    display: block;
    flex-shrink: 0;
}
.article-cover-thumb--empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #edf2f7;
    color: #a0aec0;
}
.article-cover-thumb--empty svg {
    width: 42%;
    height: auto;
}
[data-theme="dark"] .article-cover-thumb--empty {
    background: #1f1f1f;
    color: #666;
}
</style>
