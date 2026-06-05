<script setup>
defineProps({
    tag: { type: Object, required: true },
})
</script>

<template>
    <span class="tag-with-tooltip">
        <span class="tag-label">{{ tag.name }}</span>
        <span v-if="tag.description" class="tag-tooltip" role="tooltip">{{ tag.description }}</span>
    </span>
</template>

<style scoped>
.tag-with-tooltip {
    position: relative;
    display: inline-block;
}
.tag-label {
    display: inline-block;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    background: #edf2f7;
    color: #4a5568;
    cursor: default;
}
.tag-with-tooltip:hover .tag-label {
    background: #e2e8f0;
}
.tag-tooltip {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    z-index: 30;
    min-width: 160px;
    max-width: 280px;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    background: #1a202c;
    color: #f7fafc;
    font-size: 0.8rem;
    font-weight: 500;
    line-height: 1.4;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.15s, visibility 0.15s;
}
.tag-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1a202c;
}
.tag-with-tooltip:hover .tag-tooltip,
.tag-with-tooltip:focus-within .tag-tooltip {
    opacity: 1;
    visibility: visible;
}
[data-theme="dark"] .tag-label {
    background: #2a2a2a;
    color: #e2e8f0;
}
[data-theme="dark"] .tag-with-tooltip:hover .tag-label {
    background: #333;
}
[data-theme="dark"] .tag-tooltip {
    background: #f0f0f0;
    color: #0a0a0a;
}
[data-theme="dark"] .tag-tooltip::after {
    border-top-color: #f0f0f0;
}
</style>
