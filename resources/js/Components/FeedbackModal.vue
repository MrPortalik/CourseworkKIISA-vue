<script setup>
import { computed, ref, watch } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import ModalPanel from '@/Components/ModalPanel.vue'
import { imageAccept, validateImageFiles } from '@/lib/imageUpload'

const props = defineProps({
    open: { type: Boolean, default: false },
    type: { type: String, default: 'feedback' },
    articleId: { type: Number, default: null },
    reportedUserId: { type: Number, default: null },
    title: { type: String, default: 'Обратная связь' },
    allowTypeSelect: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)

const selectableTypes = [
    { value: 'feedback', label: 'Обратная связь' },
    { value: 'site_complaint', label: 'Жалоба на сайт' },
]

const attachmentInput = ref(null)
const selectedFiles = ref([])
const attachmentError = ref('')

const form = useForm({
    type: props.type,
    article_id: props.articleId,
    reported_user_id: props.reportedUserId,
    message: '',
    attachments: [],
})

const resetAttachments = () => {
    selectedFiles.value = []
    attachmentError.value = ''
    form.attachments = []
    if (attachmentInput.value) {
        attachmentInput.value.value = ''
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        form.type = props.type
        form.article_id = props.articleId
        form.reported_user_id = props.reportedUserId
    } else {
        form.reset()
        form.clearErrors()
        resetAttachments()
    }
})

const modalTitle = computed(() => {
    if (props.type === 'article_complaint') return 'Пожаловаться на статью'
    if (props.type === 'user_complaint') return 'Пожаловаться на пользователя'
    if (props.type === 'site_complaint') return 'Жалоба на сайт'
    return props.title
})

const showTypeSelect = computed(() => props.allowTypeSelect && !props.articleId && !props.reportedUserId)

const formatFileSize = (bytes) => {
    if (bytes < 1024) return `${bytes} Б`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} КБ`
    return `${(bytes / (1024 * 1024)).toFixed(1)} МБ`
}

const totalAttachmentSize = computed(() =>
    selectedFiles.value.reduce((sum, file) => sum + file.size, 0),
)

const onAttachmentsChange = (event) => {
    const files = [...(event.target.files || [])]
    const error = validateImageFiles(files)
    if (error) {
        attachmentError.value = error
        resetAttachments()
        return
    }

    attachmentError.value = ''
    selectedFiles.value = files
    form.attachments = files
}

const removeAttachment = (index) => {
    selectedFiles.value = selectedFiles.value.filter((_, i) => i !== index)
    form.attachments = [...selectedFiles.value]
    attachmentError.value = validateImageFiles(selectedFiles.value) || ''
}

const submit = () => {
    const error = validateImageFiles(selectedFiles.value)
    if (error) {
        attachmentError.value = error
        return
    }

    form.type = showTypeSelect.value ? form.type : props.type
    form.article_id = props.articleId
    form.reported_user_id = props.reportedUserId
    form.attachments = [...selectedFiles.value]

    form.post(route('reports.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset()
            form.type = props.type
            resetAttachments()
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
            <div v-if="showTypeSelect" class="type-select">
                <label class="form-label" for="report-type">Категория</label>
                <select id="report-type" v-model="form.type" class="form-select" required>
                    <option v-for="option in selectableTypes" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="form.errors.type" class="form-error">{{ form.errors.type }}</p>
            </div>

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

            <div class="attachments-block">
                <label class="form-label" for="feedback-attachments">Изображения (необязательно)</label>
                <input
                    id="feedback-attachments"
                    ref="attachmentInput"
                    type="file"
                    :accept="imageAccept"
                    multiple
                    class="file-input"
                    @change="onAttachmentsChange"
                />
                <p class="attachments-hint">JPEG, PNG или WebP. Общий размер до 10 МБ.</p>
                <p v-if="attachmentError" class="form-error">{{ attachmentError }}</p>
                <p v-if="form.errors.attachments" class="form-error">{{ form.errors.attachments }}</p>
                <p v-if="form.errors['attachments.0']" class="form-error">{{ form.errors['attachments.0'] }}</p>

                <ul v-if="selectedFiles.length" class="attachment-list">
                    <li v-for="(file, index) in selectedFiles" :key="`${file.name}-${index}`" class="attachment-item">
                        <span class="attachment-name">{{ file.name }}</span>
                        <span class="attachment-size">{{ formatFileSize(file.size) }}</span>
                        <button type="button" class="attachment-remove" @click="removeAttachment(index)">×</button>
                    </li>
                </ul>
                <p v-if="selectedFiles.length" class="attachments-total">
                    Всего: {{ formatFileSize(totalAttachmentSize) }}
                </p>
            </div>
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

.type-select {
    display: grid;
    gap: 0.35rem;
}

.form-label {
    font-weight: 600;
}

.form-select,
.form-textarea,
.file-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    resize: vertical;
    font: inherit;
    box-sizing: border-box;
}

.file-input {
    padding: 0.55rem 0.75rem;
    background: #fff;
}

.attachments-block {
    display: grid;
    gap: 0.4rem;
}

.attachments-hint,
.attachments-total {
    margin: 0;
    font-size: 0.85rem;
    color: #718096;
}

.attachment-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 0.35rem;
}

.attachment-item {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 0.5rem;
    align-items: center;
    padding: 0.45rem 0.6rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
}

.attachment-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.9rem;
}

.attachment-size {
    font-size: 0.8rem;
    color: #718096;
    white-space: nowrap;
}

.attachment-remove {
    border: none;
    background: transparent;
    color: #c53030;
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
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

[data-theme="dark"] .form-textarea,
[data-theme="dark"] .form-select,
[data-theme="dark"] .file-input {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}

[data-theme="dark"] .attachment-item {
    background: #141414;
    border-color: #404040;
}

[data-theme="dark"] .attachments-hint,
[data-theme="dark"] .attachments-total,
[data-theme="dark"] .attachment-size {
    color: #aaa;
}

[data-theme="dark"] .btn-secondary {
    background: #2a2a2a;
    color: #f0f0f0;
}
</style>
