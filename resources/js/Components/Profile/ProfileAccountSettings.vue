<script setup>
import ModalPanel from '@/Components/ModalPanel.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { nextTick, ref, watch } from 'vue'

const props = defineProps({
    open: { type: Boolean, default: false },
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: null },
})

const emit = defineEmits(['close'])

const page = usePage()
const user = page.props.auth.user

const profileForm = useForm({
    name: user.name,
    email: user.email,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const deleteForm = useForm({ password: '' })

const showDeleteModal = ref(false)
const deletePasswordInput = ref(null)

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        const current = page.props.auth.user
        profileForm.name = current.name
        profileForm.email = current.email || ''
    }
})

const updateProfile = () => {
    profileForm.patch(route('profile.update'), { preserveScroll: true })
}

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

const openDeleteModal = () => {
    showDeleteModal.value = true
    nextTick(() => deletePasswordInput.value?.focus())
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    deleteForm.reset()
}

const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onFinish: () => deleteForm.reset(),
    })
}
</script>

<template>
    <ModalPanel title="Настройки аккаунта" :open="open" @close="emit('close')">
        <form class="settings-block" @submit.prevent="updateProfile">
            <h4 class="settings-subheading">Имя и email</h4>
            <label class="settings-label" for="profile-name">Имя</label>
            <input id="profile-name" v-model="profileForm.name" type="text" class="settings-input" required autocomplete="name" />
            <p v-if="profileForm.errors.name" class="settings-error">{{ profileForm.errors.name }}</p>

            <label class="settings-label" for="profile-email">Email</label>
            <input id="profile-email" v-model="profileForm.email" type="email" class="settings-input" autocomplete="username" />
            <p v-if="profileForm.errors.email" class="settings-error">{{ profileForm.errors.email }}</p>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verify-note">
                Email не подтверждён.
                <Link :href="route('verification.send')" method="post" as="button" class="verify-link">Отправить письмо повторно</Link>
            </div>
            <p v-if="status === 'verification-link-sent'" class="settings-success">Ссылка для подтверждения отправлена.</p>

            <button type="submit" class="settings-btn btn-accent" :disabled="profileForm.processing">Сохранить</button>
            <p v-if="profileForm.recentlySuccessful" class="settings-success">Сохранено.</p>
        </form>

        <form class="settings-block" @submit.prevent="updatePassword">
            <h4 class="settings-subheading">Смена пароля</h4>
            <label class="settings-label" for="current-password">Текущий пароль</label>
            <input id="current-password" v-model="passwordForm.current_password" type="password" class="settings-input" autocomplete="current-password" />
            <p v-if="passwordForm.errors.current_password" class="settings-error">{{ passwordForm.errors.current_password }}</p>

            <label class="settings-label" for="new-password">Новый пароль</label>
            <input id="new-password" v-model="passwordForm.password" type="password" class="settings-input" autocomplete="new-password" />
            <p v-if="passwordForm.errors.password" class="settings-error">{{ passwordForm.errors.password }}</p>

            <label class="settings-label" for="confirm-password">Подтвердите пароль</label>
            <input id="confirm-password" v-model="passwordForm.password_confirmation" type="password" class="settings-input" autocomplete="new-password" />
            <p v-if="passwordForm.errors.password_confirmation" class="settings-error">{{ passwordForm.errors.password_confirmation }}</p>

            <button type="submit" class="settings-btn btn-accent" :disabled="passwordForm.processing">Обновить пароль</button>
            <p v-if="passwordForm.recentlySuccessful" class="settings-success">Пароль обновлён.</p>
        </form>

        <div class="settings-block settings-block--danger">
            <h4 class="settings-subheading">Удаление аккаунта</h4>
            <p class="settings-note">Аккаунт и все связанные данные будут удалены безвозвратно.</p>
            <button type="button" class="settings-btn settings-btn--danger" @click="openDeleteModal">Удалить аккаунт</button>
        </div>

        <div v-if="showDeleteModal" class="delete-overlay" @click.self="closeDeleteModal">
            <form class="delete-modal" @submit.prevent="deleteAccount">
                <h4>Удалить аккаунт?</h4>
                <p class="settings-note">Введите пароль для подтверждения.</p>
                <input
                    ref="deletePasswordInput"
                    v-model="deleteForm.password"
                    type="password"
                    class="settings-input"
                    placeholder="Пароль"
                    required
                    autocomplete="current-password"
                />
                <p v-if="deleteForm.errors.password" class="settings-error">{{ deleteForm.errors.password }}</p>
                <div class="delete-actions">
                    <button type="button" class="settings-btn btn-accent" @click="closeDeleteModal">Отмена</button>
                    <button type="submit" class="settings-btn settings-btn--danger" :disabled="deleteForm.processing">Удалить</button>
                </div>
            </form>
        </div>
    </ModalPanel>
</template>

<style scoped>
.settings-block {
    margin-bottom: 1.75rem;
    padding-bottom: 1.75rem;
    border-bottom: 1px solid #e2e8f0;
}
.settings-block--danger {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}
.settings-subheading {
    margin: 0 0 0.85rem;
    font-size: 1.05rem;
    font-weight: 600;
}
.settings-label {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
}
.settings-input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font: inherit;
    box-sizing: border-box;
    margin-bottom: 0.65rem;
}
.settings-btn {
    margin-top: 0.5rem;
}
.settings-btn--danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    width: 100%;
    min-height: 44px;
    padding: 0.65rem 1.35rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.95rem;
    border: 2px solid #e53e3e;
    background: transparent;
    color: #e53e3e;
    cursor: pointer;
    font-family: inherit;
    line-height: 1.2;
    transition: background 0.2s, color 0.2s;
}
.settings-btn--danger:hover {
    background: #e53e3e;
    color: #fff;
}
.settings-error {
    color: #c53030;
    font-size: 0.875rem;
    margin: -0.35rem 0 0.5rem;
}
.settings-success {
    color: #38a169;
    font-size: 0.875rem;
    margin: 0.5rem 0 0;
}
.settings-note {
    color: #718096;
    font-size: 0.9rem;
    margin: 0 0 0.75rem;
    line-height: 1.5;
}
.verify-note {
    font-size: 0.875rem;
    margin-bottom: 0.65rem;
}
.verify-link {
    background: none;
    border: none;
    color: #0db7ff;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
    font: inherit;
}
.delete-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 300;
    padding: 1rem;
}
.delete-modal {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    width: min(420px, 100%);
}
.delete-modal h4 {
    margin: 0 0 0.5rem;
}
.delete-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}
.delete-actions .settings-btn {
    width: auto;
    flex: 1;
}
[data-theme="dark"] .settings-block {
    border-color: #333;
}
[data-theme="dark"] .settings-input {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}
[data-theme="dark"] .settings-note {
    color: #aaa;
}
[data-theme="dark"] .delete-modal {
    background: #141414;
    color: #f0f0f0;
}
[data-theme="dark"] .settings-btn--danger {
    background: transparent;
    color: #fc8181;
    border-color: #fc8181;
}
[data-theme="dark"] .settings-btn--danger:hover {
    background: #e53e3e;
    color: #fff;
    border-color: #e53e3e;
}
</style>
