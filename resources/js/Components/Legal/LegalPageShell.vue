<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import PageWithSidebar from '@/Layouts/PageWithSidebar.vue'
import { legalPageNav } from '@/lib/legalPages'

const props = defineProps({
    pageKey: { type: String, required: true },
    title: { type: String, required: true },
    description: { type: String, required: true },
    sections: { type: Array, required: true },
})

const nav = computed(() => legalPageNav(props.pageKey))
</script>

<template>
    <PageHead :title="title" :description="description" />

    <PageWithSidebar>
        <article class="legal-page">
            <h1>{{ title }}</h1>

            <div class="legal-page__body">
                <section v-for="(section, index) in sections" :key="index" class="legal-page__section">
                    <h2>{{ section.title }}</h2>
                    <p v-for="(paragraph, pIndex) in section.paragraphs" :key="pIndex">{{ paragraph }}</p>
                </section>
            </div>

            <nav class="legal-page__nav" aria-label="Навигация по юридическим документам">
                <Link :href="route(nav.prev.route)" class="legal-page__nav-link legal-page__nav-link--prev content-link">
                    {{ nav.prev.prevLabel }}
                </Link>
                <Link :href="route(nav.next.route)" class="legal-page__nav-link legal-page__nav-link--next content-link">
                    {{ nav.next.nextLabel }}
                </Link>
            </nav>
        </article>
    </PageWithSidebar>
</template>

<style scoped>
.legal-page h1 {
    margin: 0 0 1.5rem;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    line-height: 1.2;
}

.legal-page__body {
    max-width: 800px;
    font-size: 1rem;
    line-height: 1.65;
    color: var(--page-text, #2d3748);
}

.legal-page__section + .legal-page__section {
    margin-top: 1.5rem;
}

.legal-page__section h2 {
    margin: 0 0 0.65rem;
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.35;
}

.legal-page__section p {
    margin: 0 0 0.65rem;
    font-size: inherit;
    font-weight: 400;
    line-height: inherit;
}

.legal-page__nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1rem 1.5rem;
    margin-top: 2.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
    max-width: 800px;
}

.legal-page__nav-link {
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
}

.legal-page__nav-link--next {
    margin-left: auto;
    text-align: right;
}

[data-theme="dark"] .legal-page__body {
    color: #e2e8f0;
}

[data-theme="dark"] .legal-page__nav {
    border-color: #404040;
}

@media (max-width: 640px) {
    .legal-page__nav {
        flex-direction: column;
    }

    .legal-page__nav-link--next {
        margin-left: 0;
        text-align: left;
    }
}
</style>
