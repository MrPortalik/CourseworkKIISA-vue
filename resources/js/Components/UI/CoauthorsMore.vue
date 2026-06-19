<script setup>
import { computed } from 'vue'

const props = defineProps({
    coauthors: { type: Array, default: () => [] },
})

const names = computed(() => props.coauthors.map((person) => person.name))
</script>

<template>
    <span v-if="coauthors.length" class="coauthors-more-wrap">
        <span class="coauthors-more">и др.</span>
        <span class="coauthors-tooltip" role="tooltip">
            <span v-for="(name, index) in names" :key="index" class="coauthors-tooltip-line">{{ name }}</span>
        </span>
    </span>
</template>

<style scoped>
.coauthors-more-wrap {
    position: relative;
    display: inline-block;
    flex-shrink: 0;
}
.coauthors-more {
    cursor: default;
    white-space: nowrap;
}
.coauthors-tooltip {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    z-index: 30;
    min-width: 120px;
    max-width: 240px;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    background: #1a202c;
    color: #f7fafc;
    font-size: 0.78rem;
    font-weight: 500;
    line-height: 1.45;
    text-align: left;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.15s, visibility 0.15s;
}
.coauthors-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1a202c;
}
.coauthors-tooltip-line {
    display: block;
}
.coauthors-more-wrap:hover .coauthors-tooltip,
.coauthors-more-wrap:focus-within .coauthors-tooltip {
    opacity: 1;
    visibility: visible;
}
[data-theme="dark"] .coauthors-tooltip {
    background: #f0f0f0;
    color: #0a0a0a;
}
[data-theme="dark"] .coauthors-tooltip::after {
    border-top-color: #f0f0f0;
}
</style>
