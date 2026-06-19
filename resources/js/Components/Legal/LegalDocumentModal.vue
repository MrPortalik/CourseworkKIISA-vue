<script setup>
import { Link } from '@inertiajs/vue3'
import ModalPanel from '@/Components/ModalPanel.vue'

defineProps({
    title: { type: String, required: true },
    sections: { type: Array, required: true },
    open: { type: Boolean, default: false },
    crossLinks: { type: Array, default: () => [] },
    standaloneHref: { type: String, default: null },
})

defineEmits(['close', 'navigate'])
</script>

<template>
    <ModalPanel :title="title" :open="open" @close="$emit('close')">
        <template #header-actions>
            <Link
                v-if="standaloneHref"
                :href="standaloneHref"
                class="legal-standalone-link content-link"
                target="_blank"
            >
                Открыть на отдельной странице →
            </Link>
        </template>

        <div class="legal-doc">
            <section v-for="(section, index) in sections" :key="index" class="legal-section">
                <h4>{{ section.title }}</h4>
                <p v-for="(paragraph, pIndex) in section.paragraphs" :key="pIndex">{{ paragraph }}</p>
            </section>
        </div>
        <template #footer>
            <div v-if="crossLinks.length" class="legal-cross-links">
                <button
                    v-for="link in crossLinks"
                    :key="link.key"
                    type="button"
                    class="legal-cross-link"
                    @click="$emit('navigate', link.key)"
                >
                    {{ link.label }}
                </button>
            </div>
            <button type="button" class="legal-close-btn" @click="$emit('close')">Закрыть</button>
        </template>
    </ModalPanel>
</template>

<style scoped>
.legal-standalone-link {
    white-space: nowrap;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
}
.legal-doc {
    display: grid;
    gap: 1rem;
    max-height: 55vh;
    overflow-y: auto;
    padding-right: 0.25rem;
    font-size: 1rem;
    line-height: 1.55;
}
.legal-section h4 {
    margin: 0 0 0.45rem;
    font-size: 1.05rem;
    font-weight: 600;
}
.legal-section p {
    margin: 0 0 0.55rem;
    color: inherit;
    font-size: inherit;
    font-weight: 400;
    line-height: inherit;
}
.legal-cross-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    margin-right: auto;
}
.legal-cross-link {
    background: none;
    border: none;
    padding: 0;
    color: #0db7ff;
    font: inherit;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
}
.legal-close-btn {
    background: #0db7ff;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.25rem;
    font-weight: 700;
    cursor: pointer;
}
[data-theme="dark"] .legal-cross-link {
    color: #67e8f9;
}
[data-theme="dark"] .legal-close-btn {
    background: #ffffff;
    color: #151515;
}

@media (max-width: 640px) {
    .legal-standalone-link {
        white-space: normal;
        text-align: right;
        max-width: 9.5rem;
        line-height: 1.3;
    }
}
</style>
