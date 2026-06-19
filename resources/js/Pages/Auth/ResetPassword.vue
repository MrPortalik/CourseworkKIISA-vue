<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Новый пароль"
            description="Задайте новый пароль для входа на информационный портал КИИСА."
        />

        <h1 class="auth-title">Новый пароль</h1>
        <p class="auth-subtitle">Придумайте новый пароль для входа</p>

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
                <label for="password" class="form-label">Новый пароль</label>
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
                <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
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
                Сохранить пароль
            </button>

            <p class="auth-footer">
                <Link :href="route('login')" class="auth-link">← Вернуться ко входу</Link>
            </p>
        </form>
    </GuestLayout>
</template>
