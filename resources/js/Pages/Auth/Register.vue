<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Регистрация" />

        <h1 class="auth-title">Регистрация</h1>
        <p class="auth-subtitle">Создайте аккаунт на портале КИИСА</p>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-group">
                <label for="name" class="form-label">Имя</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    required
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
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-input"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" class="auth-submit" :disabled="form.processing">
                Зарегистрироваться
            </button>

            <p class="auth-footer">
                Уже есть аккаунт?
                <Link :href="route('login')" class="auth-link">Войти</Link>
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
.auth-form { display: flex; flex-direction: column; gap: 0.25rem; }
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
    margin-top: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    background: var(--theme_black);
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
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
    margin-left: 0.25rem;
}
.auth-link:hover { text-decoration: underline; }
[data-theme="dark"] .auth-subtitle,
[data-theme="dark"] .auth-footer { color: #aaa; }
[data-theme="dark"] .form-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
</style>
