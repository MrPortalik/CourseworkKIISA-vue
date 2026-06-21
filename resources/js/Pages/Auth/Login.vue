<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
    blockError: { type: Object, default: null },
})

const formatBlockUntil = (iso) => {
    if (!iso) return ''
    return new Date(iso).toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Вход"
            description="Войдите в аккаунт на информационном портале КИИСА, чтобы публиковать статьи и оставлять комментарии."
        />

        <h1 class="auth-title">Вход</h1>
        <p class="auth-subtitle">Войдите в аккаунт на портале КИИСА</p>

        <div v-if="status" class="auth-status">{{ status }}</div>

        <div v-if="blockError" class="auth-block">
            <p class="auth-block-title">Аккаунт заблокирован</p>
            <p class="auth-block-reason">{{ blockError.reason }}</p>
            <p v-if="blockError.permanent" class="auth-block-until">Перманентная блокировка</p>
            <p v-else-if="blockError.blocked_until" class="auth-block-until">
                Разблокировка: {{ formatBlockUntil(blockError.blocked_until) }}
            </p>
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <CatCheckbox v-model="form.remember" class="remember-row">
                Запомнить меня
            </CatCheckbox>

            <button type="submit" class="auth-submit" :disabled="form.processing">Войти</button>

            <p class="auth-footer">
                <Link v-if="canResetPassword" :href="route('password.request')" class="auth-link">
                    Забыли пароль?
                </Link>
                <span v-if="canResetPassword"> · </span>
                <Link :href="route('register')" class="auth-link">Регистрация</Link>
            </p>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-title {
    margin: 0 0 0.35rem;
    font-size: 2rem;
    text-align: center;
}
.auth-subtitle {
    margin: 0 0 1.75rem;
    text-align: center;
    font-size: 1rem;
    color: #718096;
}
.auth-status {
    margin-bottom: 1rem;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    background: #c6f6d5;
    color: #22543d;
    font-size: 0.9rem;
}
.auth-block {
    margin-bottom: 1rem;
    padding: 0.85rem 1rem;
    border-radius: 8px;
    background: #fed7d7;
    color: #742a2a;
    font-size: 0.9rem;
}
.auth-block-title {
    margin: 0 0 0.35rem;
    font-weight: 700;
}
.auth-block-reason,
.auth-block-until {
    margin: 0.25rem 0 0;
    line-height: 1.45;
}
.auth-form { display: flex; flex-direction: column; }
.form-group { margin-bottom: 1rem; }
.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.4rem;
    font-size: 0.95rem;
}
.form-input {
    width: 100%;
    padding: 0.7rem 0.85rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    background: #fff;
    color: inherit;
}
.remember-row {
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #4a5568;
}
.auth-submit {
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    background: #4299e1;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.auth-submit:hover:not(:disabled) {
    background: #3182ce;
}
.auth-submit:disabled { opacity: 0.6; cursor: wait; }
.auth-footer {
    margin: 1.25rem 0 0;
    text-align: center;
    font-size: 0.95rem;
    color: #718096;
}
.auth-link {
    color: #0db7ff;
    font-weight: 600;
    text-decoration: none;
}
.auth-link:hover { text-decoration: underline; }
[data-theme="dark"] .auth-subtitle,
[data-theme="dark"] .auth-footer,
[data-theme="dark"] .remember-row { color: #aaa; }
[data-theme="dark"] .auth-block {
    background: rgba(229, 62, 62, 0.15);
    color: #feb2b2;
}
[data-theme="dark"] .form-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .auth-submit {
    background: #ffffff;
    color: #151515;
}
[data-theme="dark"] .auth-submit:hover:not(:disabled) {
    background: #f0f0f0;
}
</style>
