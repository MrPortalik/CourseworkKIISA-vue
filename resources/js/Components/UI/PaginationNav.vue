<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { filterPaginationLinks, formatPaginationLabel } from '@/lib/pagination'

const props = defineProps({
    links: { type: Array, default: () => [] },
    lastPage: { type: Number, default: null },
    minLinks: { type: Number, default: 4 },
    preserveScroll: { type: Boolean, default: true },
})

const visibleLinks = computed(() => {
    const links = props.links ?? []
    if (links.length < props.minLinks) {
        return []
    }

    return filterPaginationLinks(links, props.lastPage)
})
</script>

<template>
    <nav v-if="visibleLinks.length" class="pagination">
        <Link
            v-for="(link, index) in visibleLinks"
            :key="`${link.label}-${index}`"
            :href="link.url || '#'"
            class="page-link"
            :class="{ active: link.active, disabled: !link.url }"
            :preserve-scroll="preserveScroll"
            v-html="formatPaginationLabel(link.label)"
        />
    </nav>
</template>
