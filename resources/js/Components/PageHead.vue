<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { truncateSeoDescription, truncateSeoTitle } from '@/lib/seo'

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, required: true },
})

const seoTitle = computed(() => truncateSeoTitle(props.title))
const seoDescription = computed(() => truncateSeoDescription(props.description))
</script>

<template>
    <Head :title="seoTitle">
        <meta head-key="description" name="description" :content="seoDescription" />
    </Head>
</template>
