<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

defineProps({
    status: { type: String, default: null },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Забыли пароль"
            description="Восстановите доступ к аккаунту на портале КИИСА: укажите email для ссылки сброса пароля."
        />

        <h1 class="auth-title">Сброс пароля</h1>
        <p class="auth-subtitle">Восстановление доступа к аккаунту</p>

        <p class="auth-warning">
            На данный момент у портала нет собственной почты, а соответственно данный функционал не работает.
            В форме обратной связи обратитесь по поводу смены пароля или почты с доказательством,
            подтверждающим владение почтой, по которой аккаунт зарегистрирован.
        </p>

        <p class="auth-intro">
            Укажите email, который вы использовали при регистрации. Мы отправим ссылку для создания нового пароля.
        </p>

        <div v-if="status" class="auth-status">{{ status }}</div>

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

            <button type="submit" class="auth-submit" :disabled="form.processing">
                Отправить ссылку
            </button>

            <p class="auth-footer">
                <Link :href="route('login')" class="auth-link">← Вернуться ко входу</Link>
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
    margin: 0 0 1rem;
    text-align: center;
    font-size: 1rem;
    color: #718096;
}
.auth-warning {
    margin: 0 0 1.25rem;
    padding: 0.85rem 1rem;
    border-radius: 8px;
    background: #fff5f5;
    border: 1px solid #feb2b2;
    color: #c53030;
    font-size: 0.92rem;
    line-height: 1.5;
}
.auth-intro {
    margin: 0 0 1.25rem;
    text-align: center;
    color: #718096;
    line-height: 1.5;
    font-size: 0.95rem;
}
.auth-status {
    margin-bottom: 1rem;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    background: #c6f6d5;
    color: #22543d;
    font-size: 0.9rem;
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
}
.auth-footer {
    margin: 1.25rem 0 0;
    text-align: center;
    font-size: 0.95rem;
}
.auth-link {
    color: #0db7ff;
    font-weight: 600;
    text-decoration: none;
}
[data-theme="dark"] .auth-subtitle,
[data-theme="dark"] .auth-intro,
[data-theme="dark"] .auth-footer { color: #aaa; }
[data-theme="dark"] .auth-warning {
    background: rgba(229, 62, 62, 0.12);
    border-color: rgba(252, 129, 129, 0.45);
    color: #fc8181;
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
</style>
