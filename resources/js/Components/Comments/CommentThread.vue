<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import CommentItem from './CommentItem.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'

const props = defineProps({
    comments: { type: Array, default: () => [] },
    articleSlug: { type: String, required: true },
    articleAuthorId: { type: Number, default: null },
    userCommentVotes: { type: Object, default: () => ({}) },
})

const page = usePage()
const replyTo = ref(null)

const findComment = (id) => {
    for (const c of props.comments) {
        if (c.id === id) return c
        for (const child of c.children ?? []) {
            if (child.id === id) return child
            for (const grand of child.children ?? []) {
                if (grand.id === id) return grand
            }
        }
    }
    return null
}

const replyToAuthor = computed(() => {
    if (!replyTo.value) return ''
    return findComment(replyTo.value)?.user?.name ?? ''
})
const form = useForm({ body: '', parent_id: null })


const submit = () => {
    form.parent_id = replyTo.value
    form.post(route('articles.comments.store', props.articleSlug), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            replyTo.value = null
        },
    })
}

const voteComment = (commentId, vote) => {
    if (!page.props.auth?.user) return
    router.post(route('comments.vote', commentId), { vote }, { preserveScroll: true })
}

const startReply = (id) => {
    replyTo.value = id
}

const deleteComment = (commentId) => {
    if (!confirm('Удалить комментарий?')) return
    router.delete(route('comments.destroy', commentId), { preserveScroll: true })
}
</script>

<template>
    <section class="comments-block">
        <h3 class="comments-title">{{ comments.length }} комментариев</h3>

        <form v-if="$page.props.auth?.user" class="comment-form" @submit.prevent="submit">
            <UserAvatar :src="$page.props.auth?.user?.avatar" :alt="$page.props.auth?.user?.name" :size="40" class="comment-avatar" />
            <div class="comment-input-wrap">
                <p v-if="replyTo" class="reply-hint">Ответ на комментарий {{ replyToAuthor }}</p>
                <textarea v-model="form.body" rows="3" placeholder="Оставьте комментарий..." required />
                <div class="comment-form-actions">
                    <button v-if="replyTo" type="button" class="btn-text" @click="replyTo = null">Отмена</button>
                    <button type="submit" class="btn-submit" :disabled="form.processing">Комментировать</button>
                </div>
            </div>
        </form>

        <ul class="comment-list">
            <li v-for="comment in comments" :key="comment.id" class="comment-root">
                <CommentItem
                    :comment="comment"
                    :article-author-id="articleAuthorId"
                    :user-vote="userCommentVotes[comment.id]"
                    @reply="startReply"
                    @vote="voteComment"
                    @delete="deleteComment"
                />
                <ul v-if="comment.children?.length" class="comment-replies">
                    <li v-for="child in comment.children" :key="child.id">
                        <CommentItem
                            :comment="child"
                            :article-author-id="articleAuthorId"
                            :user-vote="userCommentVotes[child.id]"
                            is-reply
                            @reply="startReply"
                            @vote="voteComment"
                            @delete="deleteComment"
                        />
                        <ul v-if="child.children?.length" class="comment-replies nested">
                            <li v-for="grand in child.children" :key="grand.id">
                                <CommentItem
                                    :comment="grand"
                                    :article-author-id="articleAuthorId"
                                    :user-vote="userCommentVotes[grand.id]"
                                    is-reply
                                    @reply="startReply"
                                    @vote="voteComment"
                                    @delete="deleteComment"
                                />
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</template>

<style scoped>
.comments-block {
    width: 100%;
    margin-top: 0;
    padding-top: 0;
}
.comments-title {
    font-size: 1.35rem;
    margin-bottom: 1.5rem;
    color: var(--page-text, #1a202c);
}
.comment-form {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    width: 100%;
}
.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.comment-input-wrap {
    flex: 1;
    min-width: 0;
    width: 100%;
}
.comment-input-wrap textarea {
    width: 100%;
    box-sizing: border-box;
    border: none;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.5rem 0;
    resize: vertical;
    background: transparent;
    font-size: 0.95rem;
    color: var(--page-text, inherit);
}
.comment-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.btn-submit {
    background: #0db7ff;
    color: #fff;
    border: none;
    padding: 0.45rem 1rem;
    border-radius: 18px;
    font-weight: 600;
    cursor: pointer;
}
.btn-text { background: none; border: none; color: #718096; cursor: pointer; }
.comment-list { list-style: none; padding: 0; margin: 0; width: 100%; }
.comment-replies {
    list-style: none;
    padding: 0 0 0 2.5rem;
    margin: 0.5rem 0 0;
    border-left: 2px solid #edf2f7;
}
.comment-replies.nested { padding-left: 2rem; }
.reply-hint { font-size: 0.8rem; color: #718096; margin: 0 0 0.25rem; }
[data-theme="dark"] .comment-input-wrap textarea {
    border-color: #404040;
}
[data-theme="dark"] .comment-replies { border-color: #333; }
</style>
