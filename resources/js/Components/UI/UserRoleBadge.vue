<script setup>
import { computed } from 'vue'

const props = defineProps({
    user: { type: Object, default: null },
    articleAuthorId: { type: Number, default: null },
    variant: { type: String, default: 'default' },
})

const isOwner = computed(() => props.user?.role === 'owner')
const isAdminRole = computed(() => props.user?.role === 'admin')
const isModerator = computed(() => props.user?.role === 'moderator')
const isArticleAuthor = computed(
    () => props.articleAuthorId != null && props.user?.id === props.articleAuthorId,
)
</script>

<template>
    <span
        v-if="isArticleAuthor || isOwner || isAdminRole || isModerator"
        class="role-badges-wrap"
        :class="{ 'role-badges-wrap--profile': variant === 'profile' }"
    >
        <span v-if="isArticleAuthor" class="role-badge role-badge--author">Автор статьи</span>
        <span v-if="isOwner" class="role-badge role-badge--owner">Владелец</span>
        <span v-else-if="isAdminRole" class="role-badge role-badge--admin">Администратор</span>
        <span v-else-if="isModerator" class="role-badge role-badge--moderator">Модератор</span>
    </span>
</template>

<style scoped>
.role-badges-wrap {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
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
.role-badges-wrap--profile .role-badge {
    font-size: clamp(0.7rem, 1.2vw, 0.95rem);
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
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
.role-badge--owner {
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
}
.role-badge--moderator {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
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
