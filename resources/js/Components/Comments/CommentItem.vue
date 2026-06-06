<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import UserRoleBadge from '@/Components/UI/UserRoleBadge.vue'

const props = defineProps({
    comment: Object,
    userVote: Number,
    isReply: { type: Boolean, default: false },
    articleAuthorId: { type: Number, default: null },
})

const page = usePage()
const isOwnComment = computed(() => page.props.auth?.user?.id === props.comment.user_id)
const isAdmin = computed(() => ['admin', 'owner'].includes(page.props.auth?.user?.role))
const canDelete = computed(() => isOwnComment.value || isAdmin.value)

defineEmits(['reply', 'vote', 'delete'])

const formatDate = (d) => d ? new Date(d).toLocaleString('ru-RU') : ''

const score = computed(() => (props.comment.upvotes_count ?? 0) - (props.comment.downvotes_count ?? 0))

const scoreClass = computed(() => {
    if (score.value > 0) return 'score--positive'
    if (score.value < 0) return 'score--negative'
    return 'score--neutral'
})

const canVote = computed(() => Boolean(page.props.auth?.user) && !isOwnComment.value)
</script>

<template>
    <article class="comment-item" :class="{ reply: isReply }">
        <UserAvatar :src="comment.user?.avatar" :alt="comment.user?.name" :size="40" class="comment-avatar" />
        <div class="comment-body">
            <header class="comment-header">
                <div class="comment-author-row">
                    <Link
                        v-if="comment.user?.id"
                        :href="route('authors.show', comment.user.id)"
                        class="author-link"
                    >
                        <strong>{{ comment.user.name }}</strong>
                    </Link>
                    <strong v-else>{{ comment.user?.name }}</strong>
                    <UserRoleBadge :user="comment.user" :article-author-id="articleAuthorId" />
                </div>
                <span class="date">{{ formatDate(comment.created_at) }}</span>
            </header>
            <p class="comment-text">{{ comment.body }}</p>
            <div class="comment-actions">
                <div
                    class="vote-pill"
                    :class="{
                        'vote-pill--readonly': !canVote,
                        'vote-pill--own': isOwnComment,
                    }"
                >
                    <button
                        v-if="canVote"
                        type="button"
                        class="vote-arrow up"
                        :class="{ active: userVote === 1 }"
                        @click="$emit('vote', comment.id, 1)"
                    >
                        ▲
                    </button>
                    <span v-else class="vote-arrow vote-arrow--static up" aria-hidden="true">▲</span>
                    <span class="score" :class="scoreClass">{{ score }}</span>
                    <button
                        v-if="canVote"
                        type="button"
                        class="vote-arrow down"
                        :class="{ active: userVote === -1 }"
                        @click="$emit('vote', comment.id, -1)"
                    >
                        ▼
                    </button>
                    <span v-else class="vote-arrow vote-arrow--static down" aria-hidden="true">▼</span>
                </div>
                <button type="button" class="btn-reply" @click="$emit('reply', comment.id)">Ответить</button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="btn-delete"
                    @click="$emit('delete', comment.id)"
                >
                    Удалить
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
.comment-item {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    width: 100%;
}
.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.comment-body { flex: 1; min-width: 0; }
.comment-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    flex-wrap: wrap;
}
.comment-author-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    min-width: 0;
}
header strong { color: var(--page-text, inherit); }
.author-link {
    text-decoration: none;
    color: inherit;
}
.author-link:hover strong { text-decoration: underline; opacity: 0.85; }
.date {
    font-size: 0.75rem;
    color: #718096;
    flex-shrink: 0;
}
.comment-text {
    margin: 0 0 0.5rem;
    font-size: 0.95rem;
    line-height: 1.5;
    color: var(--page-text, inherit);
    word-break: break-word;
    overflow-wrap: anywhere;
}
.comment-actions { display: flex; align-items: center; gap: 1rem; }
.vote-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.15rem 0.35rem;
    min-width: 2rem;
}
.vote-arrow {
    background: none;
    border: none;
    cursor: pointer;
    color: #a0aec0;
    font-size: 0.65rem;
    padding: 0.1rem;
    line-height: 1;
}
.vote-arrow.up.active { color: #22c55e; }
.vote-arrow.down.active { color: #ef4444; }
.vote-pill--readonly {
    opacity: 0.85;
}
.vote-pill--own {
    background: transparent;
    border-color: #edf2f7;
    opacity: 1;
}
.vote-arrow--static {
    cursor: default;
    pointer-events: none;
}
.vote-pill--own .vote-arrow--static {
    color: #e2e8f0;
    opacity: 0.35;
    font-size: 0.55rem;
}
.vote-pill--own .score {
    color: #a0aec0 !important;
    opacity: 0.75;
}
.score {
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1.2;
}
.score--neutral { color: #4a5568; }
.score--positive { color: #22c55e; }
.score--negative { color: #ef4444; }
.btn-reply,
.btn-delete {
    background: none;
    border: none;
    color: #4a5568;
    font-weight: 600;
    font-size: 0.8rem;
    cursor: pointer;
}
.btn-delete {
    color: #e53e3e;
}
.btn-delete:hover {
    text-decoration: underline;
}
[data-theme="dark"] .vote-pill { background: #1a1a1a; border-color: #404040; }
[data-theme="dark"] .vote-pill--own {
    background: transparent;
    border-color: #2d2d2d;
}
[data-theme="dark"] .vote-pill--own .vote-arrow--static {
    color: #4a5568;
    opacity: 0.45;
}
[data-theme="dark"] .vote-pill--own .score {
    color: #718096 !important;
}
[data-theme="dark"] .score--neutral { color: #ccc; }
</style>
