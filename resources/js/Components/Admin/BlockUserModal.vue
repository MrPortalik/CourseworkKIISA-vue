<script setup>
import ModalPanel from '@/Components/ModalPanel.vue'
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    open: { type: Boolean, default: false },
    userId: { type: Number, required: true },
})

const emit = defineEmits(['close'])

const form = useForm({
    reason: '',
    is_permanent: false,
    duration_days: 1,
})

watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        form.reset()
        form.clearErrors()
    }
})

const submit = () => {
    form.post(route('admin.users.block', props.userId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            emit('close')
        },
    })
}
</script>

<template>
    <ModalPanel title="Заблокировать пользователя" :open="open" @close="emit('close')">
        <form id="block-user-form" class="block-form" @submit.prevent="submit">
            <label class="form-label" for="block-reason">Причина блокировки</label>
            <textarea
                id="block-reason"
                v-model="form.reason"
                class="form-textarea"
                rows="4"
                required
                minlength="5"
                placeholder="Укажите причину блокировки"
            />
            <p v-if="form.errors.reason" class="form-error">{{ form.errors.reason }}</p>

            <label class="checkbox-row">
                <input v-model="form.is_permanent" type="checkbox" />
                <span>Перманентная блокировка</span>
            </label>

            <div v-if="!form.is_permanent" class="duration-row">
                <label class="form-label" for="block-days">Срок (дней)</label>
                <input
                    id="block-days"
                    v-model.number="form.duration_days"
                    type="number"
                    min="1"
                    max="365"
                    class="form-input"
                    required
                />
                <p v-if="form.errors.duration_days" class="form-error">{{ form.errors.duration_days }}</p>
            </div>
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Отмена</button>
            <button type="submit" form="block-user-form" class="btn-danger" :disabled="form.processing">
                Заблокировать
            </button>
        </template>
    </ModalPanel>
</template>

<style scoped>
.block-form {
    display: grid;
    gap: 0.75rem;
}
.form-label {
    font-weight: 600;
    font-size: 0.9rem;
}
.form-textarea,
.form-input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    box-sizing: border-box;
}
.checkbox-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}
.duration-row {
    display: grid;
    gap: 0.35rem;
}
.form-error {
    color: #c53030;
    font-size: 0.875rem;
    margin: 0;
}
.btn-secondary,
.btn-danger {
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.1rem;
    font-weight: 700;
    cursor: pointer;
}
.btn-secondary {
    background: #edf2f7;
    color: #2d3748;
}
.btn-danger {
    background: #e53e3e;
    color: #fff;
}
[data-theme="dark"] .form-textarea,
[data-theme="dark"] .form-input {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}
[data-theme="dark"] .btn-secondary {
    background: #2a2a2a;
    color: #f0f0f0;
}
</style>
