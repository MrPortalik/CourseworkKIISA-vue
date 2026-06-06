<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import ModalPanel from '@/Components/ModalPanel.vue'

const props = defineProps({
    open: { type: Boolean, default: false },
    type: { type: String, default: 'feedback' },
    articleId: { type: Number, default: null },
    title: { type: String, default: 'Обратная связь' },
})

const emit = defineEmits(['close'])

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)

const form = useForm({
    type: props.type,
    article_id: props.articleId,
    message: '',
})

const modalTitle = computed(() =>
    props.type === 'article_complaint' ? 'Пожаловаться на статью' : props.title,
)

const submit = () => {
    form.type = props.type
    form.article_id = props.articleId
    form.post(route('reports.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            emit('close')
        },
    })
}
</script>

<template>
    <ModalPanel :title="modalTitle" :open="open" @close="emit('close')">
        <div v-if="!isLoggedIn" class="guest-note">
            <p>Для отправки сообщения необходимо войти в аккаунт.</p>
            <Link :href="route('login')" class="login-link">Войти</Link>
        </div>
        <form v-else id="feedback-form" class="feedback-form" @submit.prevent="submit">
            <label class="form-label" for="feedback-message">Сообщение</label>
            <textarea
                id="feedback-message"
                v-model="form.message"
                class="form-textarea"
                rows="6"
                required
                minlength="10"
                placeholder="Опишите проблему или предложение"
            />
            <p v-if="form.errors.message" class="form-error">{{ form.errors.message }}</p>
        </form>

        <template v-if="isLoggedIn" #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Отмена</button>
            <button type="submit" form="feedback-form" class="btn-primary" :disabled="form.processing">
                Отправить
            </button>
        </template>
    </ModalPanel>
</template>

<style scoped>
.guest-note {
    display: grid;
    gap: 0.75rem;
}

.login-link {
    color: #0db7ff;
    font-weight: 700;
}

.feedback-form {
    display: grid;
    gap: 0.75rem;
}

.form-label {
    font-weight: 600;
}

.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    resize: vertical;
    font: inherit;
}

.form-error {
    color: #c53030;
    margin: 0;
    font-size: 0.875rem;
}

.btn-primary,
.btn-secondary {
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.1rem;
    font-weight: 700;
    cursor: pointer;
}

.btn-primary {
    background: #0db7ff;
    color: #fff;
}

.btn-secondary {
    background: #edf2f7;
    color: #2d3748;
}

[data-theme="dark"] .form-textarea {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}

[data-theme="dark"] .btn-secondary {
    background: #2a2a2a;
    color: #f0f0f0;
}
</style>
