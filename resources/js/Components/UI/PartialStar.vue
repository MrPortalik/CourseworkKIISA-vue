<script setup>
import { computed } from 'vue'
import { formatRating } from '@/lib/formatRating'

const props = defineProps({
    rating: { type: Number, default: 0 },
    max: { type: Number, default: 5 },
    size: { type: String, default: '1.75rem' },
})

const fillPercent = computed(() => {
    const value = Math.max(0, Math.min(props.max, Number(props.rating) || 0))
    return `${(value / props.max) * 100}%`
})

const formatted = computed(() => formatRating(props.rating))
</script>

<template>
    <span class="partial-star" :style="{ fontSize: size, '--fill': fillPercent }" aria-hidden="true">
        <span class="partial-star__empty">★</span>
        <span class="partial-star__filled">★</span>
        <span class="sr-only">{{ formatted }}</span>
    </span>
</template>

<style scoped>
.partial-star {
    position: relative;
    display: inline-block;
    line-height: 1;
    width: 1em;
    height: 1em;
}
.partial-star__empty,
.partial-star__filled {
    position: absolute;
    inset: 0;
    display: block;
}
.partial-star__empty {
    color: #cbd5e0;
}
.partial-star__filled {
    color: #f6ad55;
    width: var(--fill);
    overflow: hidden;
    white-space: nowrap;
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
[data-theme="dark"] .partial-star__empty {
    color: #52525b;
}
[data-theme="dark"] .partial-star__filled {
    color: #f6ad55;
}
</style>
