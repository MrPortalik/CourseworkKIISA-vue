<script setup>
import { computed } from 'vue'

const props = defineProps({
    user: { type: Object, default: null },
    articleAuthorId: { type: Number, default: null },
})

const isAdmin = computed(() => props.user?.role === 'admin')
const isArticleAuthor = computed(
    () => props.articleAuthorId != null && props.user?.id === props.articleAuthorId,
)
</script>

<template>
    <span v-if="isArticleAuthor || isAdmin" class="role-badges-wrap">
        <span v-if="isArticleAuthor" class="role-badge role-badge--author">Автор статьи</span>
        <span v-else-if="isAdmin" class="role-badge role-badge--admin">Администратор</span>
    </span>
</template>

<style scoped>
.role-badges-wrap {
    display: inline-flex;
    align-items: center;
}
.role-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 1.2;
    padding: 0.12rem 0.45rem;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    vertical-align: middle;
    white-space: nowrap;
}
.role-badge--author {
    background: #fef9c3;
    color: #854d0e;
    border: 1px solid #fde68a;
}
.role-badge--admin {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
[data-theme="dark"] .role-badge--author {
    background: rgba(254, 249, 195, 0.2);
    color: #fde047;
    border-color: rgba(253, 224, 71, 0.45);
}
[data-theme="dark"] .role-badge--admin {
    background: rgba(254, 226, 226, 0.15);
    color: #fca5a5;
    border-color: rgba(248, 113, 113, 0.45);
}
</style>
